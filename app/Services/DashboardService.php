<?php

namespace App\Services;

use App\Models\BudgetTransfer;
use App\Models\DailyReport;
use App\Models\MonthlySetting;
use App\Models\WeeklyTarget;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected CalculationService $calc,
        protected BudgetService $budgetService,
    ) {}

    /**
     * Get all KPIs for the dashboard
     */
    public function getKpis(string $month): array
    {
        $monthDate = Carbon::parse($month)->startOfMonth();
        $setting = MonthlySetting::query()
            ->whereRaw("month = ?", [$monthDate->format('Y-m-d')])
            ->first();

        if (!$setting) {
            return $this->emptyKpis();
        }

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $reports = DailyReport::query()
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        // 1. Calculate base fallback from local PaymentSync table
        $dailyPayments = \App\Models\PaymentSync::query()
            ->selectRaw("DATE(paid_at) as pay_date, SUM(amount_paid) as total_paid")
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupByRaw("DATE(paid_at)")
            ->pluck('total_paid', 'pay_date');

        $inflowByDay = [];
        foreach ($reports as $report) {
            $dateKey = $report->date->format('Y-m-d');
            $inflowByDay[$report->date->day] = (float) $dailyPayments->get($dateKey, 0.0);
        }

        // 2. Try fetching from the finance live API
        $apiData = $this->fetchFinanceData($start, $end);
        if (!empty($apiData) && isset($apiData['chart_data']['labels']) && isset($apiData['chart_data']['cash_inflow'])) {
            $labels = $apiData['chart_data']['labels'];
            $inflow = $apiData['chart_data']['cash_inflow'];
            foreach ($labels as $index => $label) {
                $dayNum = (int) substr($label, 0, 2);
                $inflowByDay[$dayNum] = (float) ($inflow[$index] ?? 0.0);
            }
        }

        // 3. Override $report->revenue for each report
        foreach ($reports as $report) {
            $report->revenue = $inflowByDay[$report->date->day] ?? 0.0;
        }

        $totalSpent = $reports->sum(fn($r) => $r->spent);

        // Use total_cash_received from API directly if available to match exactly
        if (!empty($apiData) && isset($apiData['metrics']['total_cash_received'])) {
            $totalRevenue = (float) $apiData['metrics']['total_cash_received'];
        } else {
            $totalRevenue = $reports->sum(fn($r) => (float) $r->revenue);
        }

        $totalChatIn = $reports->sum(fn($r) => $r->chat_in);
        $totalChatConsul = $reports->sum(fn($r) => $r->chat_consul);
        $totalBudgeting = $reports->sum(fn($r) => $r->budgeting);
        $daysReported = $reports->count();

        $workingDays = $setting->working_days;
        $totalHolidays = $setting->total_holidays;

        $now = Carbon::now();
        
        // Sisa Hari (Remaining Days) calculation:
        // Use the absolute calendar instead of the number of reports submitted.
        // If a user skips submitting a report, the remaining days should still logically shrink.
        if ($monthDate->isSameMonth($now)) {
            // Sisa hari = (Total hari di bulan ini) - (Tanggal hari ini)
            $remainingDays = max(0, $monthDate->daysInMonth - $now->day);
        } elseif ($monthDate->isPast()) {
            $remainingDays = 0;
        } else {
            $remainingDays = $monthDate->daysInMonth; // Future month
        }

        // Calculate remaining_days_in_week based on user's definition (1-7, 8-14, 15-21, 22-end)
        $remainingDaysInWeek = 0;
        
        if ($now->month == $monthDate->month && $now->year == $monthDate->year) {
            $day = $now->day;
            if ($day <= 7) $endOfBlock = 7;
            elseif ($day <= 14) $endOfBlock = 14;
            elseif ($day <= 21) $endOfBlock = 21;
            elseif ($day <= 28) $endOfBlock = 28;
            else $endOfBlock = $monthDate->copy()->endOfMonth()->day;
            
            // Minimum is 0, maximum is the remaining days in this block
            $remainingDaysInWeek = max(0, $endOfBlock - $day + 1); 
        }

        // Get effective budget (including transfers)
        $effectiveBudget = $this->budgetService->getEffectiveBudget($month);

        // Core calculations
        $roas = $this->calc->roas($totalRevenue, $totalSpent);
        $greetingRate = $this->calc->greetingRate($totalChatConsul, $totalChatIn);
        $costPerChatConsul = $this->calc->costPerChatConsul($totalSpent, $totalChatConsul);
        $costPerChatIn = $this->calc->costPerChatIn($totalSpent, $totalChatIn);

        // Budget metrics
        $remainingBudget = $this->calc->remainingBudget($effectiveBudget, $totalSpent);
        $dailyBudgetTarget = $this->calc->dailyBudgetTarget($remainingBudget, $remainingDays);
        $budgetUsedPercentage = $effectiveBudget > 0 ? round(($totalSpent / $effectiveBudget) * 100, 1) : 0;

        // Revenue metrics
        $remainingRevenue = $this->calc->remainingRevenue($setting->target_revenue, $totalRevenue);
        $remainingRevenuePerDay = $this->calc->remainingRevenuePerDay($remainingRevenue, $remainingDays);
        $revenuePercentage = $setting->target_revenue > 0 ? round(($totalRevenue / $setting->target_revenue) * 100, 1) : 0;

        // Chat metrics - use first weekly target for chat target
        $weeklyTargets = WeeklyTarget::query()
            ->whereRaw("month = ?", [$monthDate->format('Y-m-d')])
            ->get();
        $totalTargetChatConsul = $weeklyTargets->sum(fn($t) => $t->target_chat_consul);
        $targetRoas = $weeklyTargets->avg(fn($t) => $t->target_roas) ?: 0;

        $remainingChat = $this->calc->remainingChat($totalTargetChatConsul, $totalChatConsul);
        $remainingChatPerDay = $this->calc->remainingChatPerDay($remainingChat, $remainingDays);
        $chatPercentage = $totalTargetChatConsul > 0 ? round(($totalChatConsul / $totalTargetChatConsul) * 100, 1) : 0;

        // Color indicators
        $roasColor = $this->calc->getRoasIndicator($roas, $targetRoas);
        $revenueColor = $this->calc->getColorIndicator($totalRevenue, $setting->target_revenue);
        $chatColor = $this->calc->getColorIndicator($totalChatConsul, $totalTargetChatConsul);
        $budgetColor = $this->calc->getBudgetIndicator($totalSpent, $effectiveBudget);

        return [
            // Summary
            'total_spent' => $totalSpent,
            'total_revenue' => $totalRevenue,
            'total_chat_in' => $totalChatIn,
            'total_chat_consul' => $totalChatConsul,
            'total_budgeting' => $totalBudgeting,
            'days_reported' => $daysReported,
            'remaining_days' => $remainingDays,
            'working_days' => $workingDays,
            'total_holidays' => $totalHolidays,
            'remaining_days_in_week' => $remainingDaysInWeek,

            // ROAS
            'roas' => $roas,
            'target_roas' => $targetRoas,
            'roas_color' => $roasColor,

            // Budget
            'effective_budget' => $effectiveBudget,
            'remaining_budget' => $remainingBudget,
            'daily_budget_target' => $dailyBudgetTarget,
            'budget_used_percentage' => $budgetUsedPercentage,
            'budget_color' => $budgetColor,

            // Revenue
            'target_revenue' => $setting->target_revenue,
            'remaining_revenue' => $remainingRevenue,
            'remaining_revenue_per_day' => $remainingRevenuePerDay,
            'revenue_percentage' => $revenuePercentage,
            'revenue_color' => $revenueColor,

            // Chat
            'target_chat_consul' => $totalTargetChatConsul,
            'remaining_chat' => $remainingChat,
            'remaining_chat_per_day' => $remainingChatPerDay,
            'chat_percentage' => $chatPercentage,
            'chat_color' => $chatColor,

            // Rates
            'greeting_rate' => $greetingRate,
            'cost_per_chat_consul' => $costPerChatConsul,
            'cost_per_chat_in' => $costPerChatIn,

            // Transfers
            'total_transfers' => $this->budgetService->getTotalTransfers($month),
        ];
    }

    /**
     * Get daily reports for the given month
     */
    public function getDailyReports(string $month): \Illuminate\Database\Eloquent\Collection
    {
        $monthDate = Carbon::parse($month);

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $reports = DailyReport::query()
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();

        // 1. Calculate base fallback from local PaymentSync table
        $dailyPayments = \App\Models\PaymentSync::query()
            ->selectRaw("DATE(paid_at) as pay_date, SUM(amount_paid) as total_paid")
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupByRaw("DATE(paid_at)")
            ->pluck('total_paid', 'pay_date');

        $inflowByDay = [];
        foreach ($reports as $report) {
            $dateKey = $report->date->format('Y-m-d');
            $inflowByDay[$report->date->day] = (float) $dailyPayments->get($dateKey, 0.0);
        }

        // 2. Try fetching from the finance live API
        $apiData = $this->fetchFinanceData($start, $end);
        if (!empty($apiData) && isset($apiData['chart_data']['labels']) && isset($apiData['chart_data']['cash_inflow'])) {
            $labels = $apiData['chart_data']['labels'];
            $inflow = $apiData['chart_data']['cash_inflow'];
            foreach ($labels as $index => $label) {
                $dayNum = (int) substr($label, 0, 2);
                $inflowByDay[$dayNum] = (float) ($inflow[$index] ?? 0.0);
            }
        }

        // 3. Override $report->revenue for each report
        foreach ($reports as $report) {
            $report->revenue = $inflowByDay[$report->date->day] ?? 0.0;
        }

        return $reports;
    }

    /**
     * Get monthly setting for the given month
     */
    public function getMonthlySetting(string $month): ?MonthlySetting
    {
        $monthDate = Carbon::parse($month);

        return MonthlySetting::query()
            ->whereRaw("month = ?", [$monthDate->format('Y-m-d')])
            ->first();
    }

    /**
     * Fetch finance API data with cache.
     */
    protected function fetchFinanceData(Carbon $start, Carbon $end): array
    {
        $cacheKey = "finance_live_api_data_{$start->toDateString()}_{$end->toDateString()}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () use ($start, $end) {
            try {
                $apiKey = config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
                $baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');
                $dashboardUrl = rtrim($baseUrl, '/') . '/finance/dashboard';

                $response = \Illuminate\Support\Facades\Http::timeout(10)->get($dashboardUrl, [
                    'api_key' => $apiKey,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['status'] ?? '') === 'success' && isset($json['data'])) {
                        return $json['data'];
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to fetch finance API data: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get empty KPIs structure
     */
    protected function emptyKpis(): array
    {
        return [
            'total_spent' => 0, 'total_revenue' => 0, 'total_chat_in' => 0,
            'total_chat_consul' => 0, 'total_budgeting' => 0, 'days_reported' => 0,
            'remaining_days' => 0, 'working_days' => 0, 'total_holidays' => 0,
            'remaining_days_in_week' => 0,
            'roas' => 0, 'target_roas' => 0, 'roas_color' => 'gray',
            'effective_budget' => 0, 'remaining_budget' => 0, 'daily_budget_target' => 0,
            'budget_used_percentage' => 0, 'budget_color' => 'gray',
            'target_revenue' => 0, 'remaining_revenue' => 0, 'remaining_revenue_per_day' => 0,
            'revenue_percentage' => 0, 'revenue_color' => 'gray',
            'target_chat_consul' => 0, 'remaining_chat' => 0, 'remaining_chat_per_day' => 0,
            'chat_percentage' => 0, 'chat_color' => 'gray',
            'greeting_rate' => 0, 'cost_per_chat_consul' => 0, 'cost_per_chat_in' => 0,
            'total_transfers' => 0,
        ];
    }
}
