<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\WeeklyTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        protected CalculationService $calc,
    ) {}

    /**
     * Get weekly aggregated report for a given month
     */
    public function getWeeklyReport(string $month): array
    {
        $monthDate = Carbon::parse($month)->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        $reports = DailyReport::whereBetween('date', [$monthDate, $endOfMonth])
            ->orderBy('date')
            ->get();

        $weeklyTargets = WeeklyTarget::query()
            ->where('month', $monthDate->format('Y-m-d'))
            ->get()
            ->keyBy('week');

        $weeks = [];
        $weekNumber = 1;
        $currentDate = $monthDate->copy();

        while ($currentDate->lte($endOfMonth)) {
            $weekStart = $currentDate->copy();
            
            $day = $weekStart->day;
            if ($day <= 7) $endDay = 7;
            elseif ($day <= 14) $endDay = 14;
            elseif ($day <= 21) $endDay = 21;
            elseif ($day <= 28) $endDay = 28;
            else $endDay = $endOfMonth->day;
            
            $weekEnd = $weekStart->copy()->day($endDay)->min($endOfMonth);

            $weekReports = $reports->filter(function (DailyReport $report) use ($weekStart, $weekEnd) {
                return $report->date->between($weekStart, $weekEnd);
            });

            $spent = $weekReports->sum('spent');
            $revenue = $weekReports->sum('revenue');
            $chatIn = $weekReports->sum('chat_in');
            $chatConsul = $weekReports->sum('chat_consul');
            $budgeting = $weekReports->sum('budgeting');

            $target = $weeklyTargets->get($weekNumber);

            $weeks[] = [
                'week' => $weekNumber,
                'start_date' => $weekStart->translatedFormat('d M'),
                'end_date' => $weekEnd->translatedFormat('d M'),
                'days' => $weekReports->count(),
                'budgeting' => $budgeting,
                'spent' => $spent,
                'revenue' => $revenue,
                'chat_in' => $chatIn,
                'chat_consul' => $chatConsul,
                'roas' => $this->calc->roas($revenue, $spent),
                'greeting_rate' => $this->calc->greetingRate($chatConsul, $chatIn),
                'cost_per_chat_consul' => $this->calc->costPerChatConsul($spent, $chatConsul),
                'cost_per_chat_in' => $this->calc->costPerChatIn($spent, $chatIn),
                // Targets
                'target_revenue' => $target?->target_revenue ?? 0,
                'target_chat_consul' => $target?->target_chat_consul ?? 0,
                'target_roas' => $target?->target_roas ?? 0,
                // Color indicators
                'revenue_color' => $this->calc->getColorIndicator($revenue, $target?->target_revenue ?? 0),
                'chat_color' => $this->calc->getColorIndicator($chatConsul, $target?->target_chat_consul ?? 0),
                'roas_color' => $this->calc->getRoasIndicator(
                    $this->calc->roas($revenue, $spent),
                    $target?->target_roas ?? 0
                ),
            ];

            $currentDate = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        return $weeks;
    }

    /**
     * Get monthly summary
     */
    public function getMonthlySummary(string $month): array
    {
        $monthDate = Carbon::parse($month);

        $reports = DailyReport::query()
            ->whereMonth('date', $monthDate->month)
            ->whereYear('date', $monthDate->year)
            ->get();

        $totalSpent = $reports->sum('spent');
        $totalRevenue = $reports->sum('revenue');
        $totalChatIn = $reports->sum('chat_in');
        $totalChatConsul = $reports->sum('chat_consul');

        return [
            'total_days' => $reports->count(),
            'total_budgeting' => $reports->sum('budgeting'),
            'total_spent' => $totalSpent,
            'total_revenue' => $totalRevenue,
            'total_chat_in' => $totalChatIn,
            'total_chat_consul' => $totalChatConsul,
            'avg_daily_spent' => $reports->count() > 0 ? round($totalSpent / $reports->count(), 2) : 0,
            'avg_daily_revenue' => $reports->count() > 0 ? round($totalRevenue / $reports->count(), 2) : 0,
            'roas' => $this->calc->roas($totalRevenue, $totalSpent),
            'greeting_rate' => $this->calc->greetingRate($totalChatConsul, $totalChatIn),
            'cost_per_chat_consul' => $this->calc->costPerChatConsul($totalSpent, $totalChatConsul),
            'cost_per_chat_in' => $this->calc->costPerChatIn($totalSpent, $totalChatIn),
        ];
    }

    /**
     * Get daily trend data for charts
     */
    public function getDailyTrend(string $month): array
    {
        $monthDate = Carbon::parse($month);

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $reports = DailyReport::query()
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        return $reports->map(function ($report) {
            return [
                'date' => $report->date->translatedFormat('d M'),
                'spent' => (float) $report->spent,
                'revenue' => (float) $report->revenue,
                'roas' => $this->calc->roas($report->revenue, $report->spent),
                'chat_in' => $report->chat_in,
                'chat_consul' => $report->chat_consul,
                'greeting_rate' => $this->calc->greetingRate($report->chat_consul, $report->chat_in),
            ];
        })->toArray();
    }
}
