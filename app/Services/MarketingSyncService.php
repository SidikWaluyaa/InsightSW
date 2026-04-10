<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\FinanceSync;
use App\Models\MetaAdsReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarketingSyncService
{
    public function __construct(
        protected MetaAdsService $metaService,
        protected DashboardApiService $apiService,
        protected SleekflowService $sleekflowService
    ) {}

    /**
     * Sync marketing data for a whole month.
     */
    public function syncMonth(string $monthYear): array
    {
        $monthDate = Carbon::parse($monthYear . '-01');
        $start = $monthDate->copy()->startOfMonth();
        $endObj = $monthDate->copy()->isCurrentMonth() ? Carbon::now() : $monthDate->copy()->endOfMonth();
        $endDateStr = $endObj->toDateString();
        $startDateStr = $start->toDateString();

        $results = ['days_processed' => 0, 'errors' => []];

        // 1. BULK FETCH: Meta Ads
        $metaDailyTotals = [];
        try {
            $adAccountId = env('META_AD_ACCOUNT_ID', 'act_1922369221497688');
            $metaDailyTotals = $this->metaService->fetchDailyAccountInsights($adAccountId, ['since' => $startDateStr, 'until' => $endDateStr]);
            Log::info("Meta Bulk Sync Complete for {$monthYear}");
        } catch (\Exception $e) {
            Log::error("Meta Bulk Sync Error: " . $e->getMessage());
        }

        // 2. BULK SYNC & FETCH: Sleekflow
        $this->sleekflowService->syncContacts($startDateStr, $endDateStr);
        $sleekflowDailyStats = $this->sleekflowService->getDailyStatsInRange($startDateStr, $endDateStr);
        Log::info("Sleekflow Bulk Sync Complete for {$monthYear}");

        // 3. BULK FETCH: Finance/Revenue
        app(FinanceSyncService::class)->sync($startDateStr, $endDateStr);
        $financeDailyTotals = FinanceSync::query()
            ->whereBetween('source_created_at', [$startDateStr . ' 00:00:00', $endDateStr . ' 23:59:59'])
            ->selectRaw("DATE(source_created_at) as date, SUM(amount_paid) as revenue")
            ->groupBy('date')
            ->get()
            ->keyBy(fn($item) => is_string($item->date) ? $item->date : Carbon::parse($item->date)->toDateString())
            ->toArray();
        Log::info("Finance Bulk Sync Complete for {$monthYear}");

        // 4. PRE-FETCH Existing Reports (to preserve budgeting)
        $existingReports = DailyReport::whereBetween('date', [$startDateStr, $endDateStr])
            ->get()
            ->keyBy(fn($item) => $item->date->toDateString());

        // 5. PREPARE UPSERT DATA
        $upsertData = [];
        $taxRate = (float) env('META_TAX_RATE', 1.11);

        for ($date = $start->copy(); $date->lte($endObj); $date->addDay()) {
            $dateStr = $date->toDateString();
            
            $rawSpent = (float) ($metaDailyTotals[$dateStr] ?? 0);
            $revenue = (float) ($financeDailyTotals[$dateStr]['revenue'] ?? 0);
            $chatIn = (int) ($sleekflowDailyStats[$dateStr]['chat_in'] ?? 0);
            $chatConsul = (int) ($sleekflowDailyStats[$dateStr]['chat_consul'] ?? 0);
            
            $existing = $existingReports->get($dateStr);

            $upsertData[] = [
                'date' => $dateStr,
                'spent' => round($rawSpent * $taxRate),
                'revenue' => $revenue,
                'chat_in' => $chatIn,
                'chat_consul' => $chatConsul,
                'budgeting' => $existing ? $existing->budgeting : 0,
                'created_at' => $existing ? $existing->created_at : now(),
                'updated_at' => now(),
            ];
            $results['days_processed']++;
        }

        // 6. BULK UPSERT
        if (!empty($upsertData)) {
            DailyReport::upsert($upsertData, ['date'], [
                'spent', 'revenue', 'chat_in', 'chat_consul', 'budgeting', 'updated_at'
            ]);
        }

        return $results;
    }

    /**
     * Sync marketing data for a specific date (Kept for small on-demand syncs).
     */
    public function syncDate(string $date, bool $useLocalOnly = false, ?float $metaDailyTotal = null): bool
    {
        // ... (Logic simplified as fallback)
        try {
            if (!$useLocalOnly) {
                $this->sleekflowService->syncContacts($date, $date);
            }
            
            $adAccountId = env('META_AD_ACCOUNT_ID', 'act_1922369221497688');
            $rawSpent = $metaDailyTotal ?? ($useLocalOnly ? MetaAdsReport::where('date', $date)->sum('spend') : 0);
            $taxRate = (float) env('META_TAX_RATE', 1.11);

            $revenue = $useLocalOnly 
                ? FinanceSync::whereBetween('source_created_at', [$date.' 00:00:00', $date.' 23:59:59'])->sum('amount_paid')
                : 0;

            $sleekflowData = $this->sleekflowService->getAnalyticsData($date, $date);
            
            DailyReport::updateOrCreate(
                ['date' => $date],
                [
                    'spent' => round($rawSpent * $taxRate),
                    'revenue' => $revenue,
                    'chat_in' => $sleekflowData['totalContacts'] ?? 0,
                    'chat_consul' => $sleekflowData['totalKonsul'] ?? 0,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error("MarketingSyncService Error for {$date}: " . $e->getMessage());
            throw $e;
        }
    }
}
