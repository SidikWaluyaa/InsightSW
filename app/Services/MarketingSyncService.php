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
        $end = $monthDate->copy()->isCurrentMonth() 
            ? Carbon::now()->format('Y-m-d') 
            : $monthDate->copy()->endOfMonth()->format('Y-m-d');

        $startDateStr = $start->toDateString();

        $results = [
            'days_processed' => 0,
            'errors' => []
        ];

        // 1. BULK SYNC: Meta Ads (Whole Month)
        $metaDailyTotals = [];
        try {
            $adAccountId = 'act_1922369221497688';
            $range = ['since' => $startDateStr, 'until' => $end];
            
            // Sync ad-level for reporting table
            $this->metaService->fetchAndSync($adAccountId, $range); 
            
            // Sync account-level for "Absolute Truth" totals
            $metaDailyTotals = $this->metaService->fetchDailyAccountInsights($adAccountId, $range);
        } catch (\Exception $e) {
            Log::error("Meta Bulk Sync Error: " . $e->getMessage());
        }

        // 2. BULK SYNC: Sleekflow (Whole Month)
        // ... (previous logic)
        try {
            $this->sleekflowService->syncContacts($startDateStr, $end);
        } catch (\Exception $e) {
            Log::error("Sleekflow Bulk Sync Error: " . $e->getMessage());
        }

        // 3. BULK SYNC: Finance/Revenue (Whole Month)
        try {
            app(FinanceSyncService::class)->sync($startDateStr, $end);
        } catch (\Exception $e) {
            Log::error("Finance Bulk Sync Error: " . $e->getMessage());
        }

        // 4. Daily Processing Loop (Now querying LOCAL DB)
        for ($date = $start->copy(); $date->lte(Carbon::parse($end)); $date->addDay()) {
            $dateStr = $date->toDateString();
            try {
                $this->syncDate($dateStr, true, $metaDailyTotals[$dateStr] ?? null); 
                $results['days_processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Error on {$dateStr}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Sync marketing data for a specific date.
     */
    public function syncDate(string $date, bool $useLocalOnly = false, ?float $metaDailyTotal = null): bool
    {
        try {
            // 1. Fetch Spent (Priority: Passed Total > Local DB > API)
            if ($metaDailyTotal !== null) {
                $rawSpent = $metaDailyTotal;
            } elseif ($useLocalOnly) {
                $rawSpent = MetaAdsReport::where('date', $date)->sum('spend');
            } else {
                $adAccountId = 'act_1922369221497688';
                $metaData = $this->metaService->fetchSummary($adAccountId, [
                    'startDate' => $date,
                    'endDate' => $date
                ]);
                $rawSpent = $metaData ? (float) ($metaData['spend'] ?? 0) : 0;
            }
            $spent = $rawSpent * 1.11;

            // 2. Fetch Revenue (Local or API)
            if ($useLocalOnly) {
                $revenue = FinanceSync::whereBetween('source_created_at', [
                    $date . ' 00:00:00', 
                    $date . ' 23:59:59'
                ])->sum('amount_paid');
            } else {
                $apiResult = $this->apiService->getDashboardSummary($date, $date, true);
                $revenue = 0;
                if (isset($apiResult['status']) && $apiResult['status'] === 'success') {
                    $revenue = (float) ($apiResult['data']['summary']['revenue'] ?? 0);
                }
            }

            // 3. Fetch Chats (Always uses local query after syncContacts)
            if (!$useLocalOnly) {
                $this->sleekflowService->syncContacts($date, $date);
            }
            $sleekflowData = $this->sleekflowService->getAnalyticsData($date, $date);
            $chatIn = $sleekflowData['totalContacts'] ?? 0;
            $chatConsul = $sleekflowData['totalKonsul'] ?? 0;

            // 4. Update DailyReport
            $report = DailyReport::where('date', $date)->first();
            
            DailyReport::updateOrCreate(
                ['date' => $date],
                [
                    'spent' => round($spent),
                    'revenue' => $revenue,
                    'chat_in' => $chatIn,
                    'chat_consul' => $chatConsul,
                    'budgeting' => $report ? $report->budgeting : 0,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error("MarketingSyncService Error for {$date}: " . $e->getMessage());
            throw $e;
        }
    }
}
