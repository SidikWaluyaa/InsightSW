<?php

namespace App\Services;

use App\Models\MetaAdsReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    protected ?string $accessToken;
    protected string $baseUrl = 'https://graph.facebook.com/v19.0';

    // Session tracker to allow additive syncing (summing FB + IG rows) within a single sync run
    protected array $initializedRecords = [];

    public function __construct()
    {
        $this->accessToken = config('services.meta.access_token') ?? env('META_ADS_ACCESS_TOKEN');
    }

    /**
     * Main Sync Function for a specific account
     */
    public function fetchAndSync(string $adAccountId, string|array $datePreset = 'last_30d')
    {
        if (!$this->accessToken) {
            Log::error("Meta Ads API Error: Access Token is missing.");
            return false;
        }

        // Reset session tracker for this run
        $this->initializedRecords = [];

        // 1. Fetch budgets for mapping
        $budgets = $this->fetchEntityBudgets($adAccountId);

        $fields = [
            'campaign_name', 'campaign_id', 'adset_name', 'adset_id', 'ad_name', 'ad_id',
            'date_start', 'impressions', 'reach', 'clicks', 'inline_link_clicks', 'spend', 'ctr', 'cpc', 'cpm', 'frequency',
            'actions', 'unique_actions'
        ];

        $params = [
            'access_token' => $this->accessToken,
            'level' => 'ad',
            'fields' => implode(',', $fields),
            'time_increment' => 1,
            'limit' => 500,
            'sort' => 'date_start_descending'
        ];

        if ($datePreset === 'full_sync') {
            $params['time_range'] = json_encode(['since' => '2026-03-01', 'until' => date('Y-m-d')]);
        } elseif (is_array($datePreset) && isset($datePreset['since'], $datePreset['until'])) {
            // Custom range array
            $params['time_range'] = json_encode($datePreset);
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePreset)) {
            // It's a specific date (YYYY-MM-DD), use time_range
            $params['time_range'] = json_encode(['since' => $datePreset, 'until' => $datePreset]);
        } else {
            // Standard presets (last_30d, yesterday, etc.)
            $params['date_preset'] = $datePreset;
        }

        $url = "{$this->baseUrl}/{$adAccountId}/insights";
        $totalSynced = 0;
        $pageCount = 0;

        do {
            Log::info("Meta Ads: Syncing page {$pageCount}...");
            $response = Http::timeout(120)->get($url, $params);

            if ($response->failed()) {
                Log::error("Meta Ads API Sync Failed: " . $response->body());
                break;
            }

            $result = $response->json();
            $data = $result['data'] ?? [];

            foreach ($data as $item) {
                $this->syncItem($item, $item['date_start'], $budgets);
                $totalSynced++;
            }

            $url = $result['paging']['next'] ?? null;
            $params = []; // Params are included in the 'next' URL
            $pageCount++;
            if ($pageCount >= 1000) break;
        } while ($url);

        // Update existing records with fresh budgets
        foreach ($budgets as $entityId => $amount) {
            MetaAdsReport::where('adset_id', $entityId)
                ->orWhere('campaign_id', $entityId)
                ->update(['budget' => $amount]);
        }

        return true;
    }

    /**
     * Aggregates Messaging Started results (Corresponds to the 197 value in Ads Manager)
     */
    protected function findResultsValue(array $actions): int
    {
        // 1. PRECISION STRIKE: The exact '197' key found in deep-inspection
        foreach ($actions as $action) {
            $type = $action['action_type'];
            if ($type === 'onsite_conversion.messaging_conversation_started_7d') {
                return (int) $action['value'];
            }
        }

        // 2. PRIMARY FALLBACK: onsite_conversion.messaging_conversation_started
        foreach ($actions as $action) {
            if ($action['action_type'] === 'onsite_conversion.messaging_conversation_started') {
                return (int) $action['value'];
            }
        }

        // 2. FALLBACK: Other messaging/conversation variants
        foreach ($actions as $action) {
            $type = strtolower($action['action_type']);
            if (str_contains($type, 'messaging_conversation_started') || 
                str_contains($type, 'messenger_conversation_started') ||
                str_contains($type, 'total_messaging_connection')) {
                return (int) $action['value'];
            }
        }

        return 0;
    }

    /**
     * Extracts values for various metrics
     */
    protected function getActionValue(array $actions, string $type): float
    {
        foreach ($actions as $action) {
            if ($action['action_type'] === $type) {
                return (float) $action['value'];
            }
        }
        return 0;
    }

    /**
     * Real-time sync logic with Additive Aggregation
     */
    protected function syncItem(array $item, string $date, array $budgets = [])
    {
        $adId = $item['ad_id'];
        $initKey = "{$adId}_{$date}";
        
        $actions = $item['actions'] ?? [];
        $uniqueActions = $item['unique_actions'] ?? [];
        
        $spend = (float) ($item['spend'] ?? 0);
        $impressions = (int) ($item['impressions'] ?? 0);
        $reach = (int) ($item['reach'] ?? 0);
        $clicks = (int) ($item['clicks'] ?? 0);
        $linkClick = (int) ($item['inline_link_clicks'] ?? 0);
        $results = $this->findResultsValue($actions);

        // Budget Mapping
        $adsetId = $item['adset_id'] ?? null;
        $campaignId = $item['campaign_id'] ?? null;
        $budget = $budgets[$adsetId] ?? ($budgets[$campaignId] ?? 0);

        if (!isset($this->initializedRecords[$initKey])) {
            // New entry for this sync session: Fresh start
            // We calculate CPC and CTR manually to ensure 100% accuracy after sums
            $ctrAll = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
            $cpcAll = $clicks > 0 ? $spend / $clicks : 0;
            
            $ctrLink = $impressions > 0 ? ($linkClick / $impressions) * 100 : 0;
            $cpcLink = $linkClick > 0 ? $spend / $linkClick : 0;

            MetaAdsReport::updateOrCreate(
                ['ad_id' => $adId, 'date' => $date],
                [
                    'campaign_name' => $item['campaign_name'],
                    'campaign_id' => $item['campaign_id'],
                    'adset_name' => $item['adset_name'],
                    'adset_id' => $item['adset_id'],
                    'ad_name' => $item['ad_name'],
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'clicks' => $clicks, // Legacy/Standard field
                    'results' => $results,
                    'budget' => $budget,
                    'ctr' => (float) $ctrLink, // Dashboard uses this for "CTR" column
                    'cpc' => (float) $cpcLink, // Dashboard uses this for "CPC" column
                    'cpm' => (float) ($item['cpm'] ?? 0),
                    'frequency' => (float) ($item['frequency'] ?? 0),
                    'link_click' => $linkClick,
                    'status' => strtoupper($item['campaign_status'] ?? 'ACTIVE'),
                    
                    // Specific "All" columns used by the web UI
                    'clicks_all' => $clicks,
                    'ctr_all' => (float) $ctrAll,
                    'cpc_all' => (float) $cpcAll,
                ]
            );
            $this->initializedRecords[$initKey] = true;
        } else {
            // Found another row (Breakdown): SUM IT UP
            $report = MetaAdsReport::where('ad_id', $adId)->where('date', $date)->first();
            if ($report) {
                $newSpend = $report->spend + $spend;
                $newImpressions = $report->impressions + $impressions;
                $newClicksAll = $report->clicks_all + $clicks;
                $newLinkClicks = $report->link_click + $linkClick;
                
                // Recalculate metrics on the fly for total parity
                $newCtrAll = $newImpressions > 0 ? ($newClicksAll / $newImpressions) * 100 : 0;
                $newCpcAll = $newClicksAll > 0 ? $newSpend / $newClicksAll : 0;
                
                $newCtrLink = $newImpressions > 0 ? ($newLinkClicks / $newImpressions) * 100 : 0;
                $newCpcLink = $newLinkClicks > 0 ? $newSpend / $newLinkClicks : 0;

                $report->update([
                    'spend' => $newSpend,
                    'impressions' => $newImpressions,
                    'reach' => $report->reach + $reach,
                    'clicks' => $newClicksAll,
                    'results' => $report->results + $results,
                    'link_click' => $newLinkClicks,
                    'ctr' => (float) $newCtrLink,
                    'cpc' => (float) $newCpcLink,
                    'clicks_all' => $newClicksAll,
                    'ctr_all' => (float) $newCtrAll,
                    'cpc_all' => (float) $newCpcAll,
                ]);
            }
        }
    }

    /**
     * Fetch budgets from entities
     */
    protected function fetchEntityBudgets(string $adAccountId): array
    {
        $budgets = [];
        try {
            // Adsets
            $res = Http::timeout(60)->get("{$this->baseUrl}/{$adAccountId}/adsets", [
                'access_token' => $this->accessToken,
                'fields' => 'id,daily_budget,lifetime_budget',
                'limit' => 1000
            ]);
            if ($res->successful()) {
                foreach ($res->json()['data'] ?? [] as $a) {
                    $budgets[$a['id']] = (float) ($a['daily_budget'] ?? ($a['lifetime_budget'] ?? 0));
                }
            }
            // Campaigns
            $res = Http::timeout(60)->get("{$this->baseUrl}/{$adAccountId}/campaigns", [
                'access_token' => $this->accessToken,
                'fields' => 'id,daily_budget,lifetime_budget',
                'limit' => 1000
            ]);
            if ($res->successful()) {
                foreach ($res->json()['data'] ?? [] as $c) {
                    $budgets[$c['id']] = (float) ($c['daily_budget'] ?? ($c['lifetime_budget'] ?? 0));
                }
            }
        } catch (\Exception $e) { Log::error("Budget fetch Error: " . $e->getMessage()); }
        return $budgets;
    }

    /**
     * Precision Hub Summary
     */
    public function fetchSummary(string $adAccountId, array $filters = [])
    {
        if (!$this->accessToken) return null;

        $params = [
            'access_token' => $this->accessToken,
            'level' => 'account', // Account level is 100% accurate and faster
            'fields' => 'reach,frequency,impressions,spend,actions,unique_actions',
        ];

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $params['time_range'] = json_encode(['since' => $filters['startDate'], 'until' => $filters['endDate']]);
        } else {
            $params['date_preset'] = 'last_30d';
        }

        $res = Http::timeout(60)->get("{$this->baseUrl}/{$adAccountId}/insights", $params);
        if ($res->failed()) return null;

        $data = $res->json()['data'] ?? [];
        $summary = ['reach' => 0, 'frequency' => 0, 'impressions' => 0, 'spend' => 0, 'results' => 0, 'link_click_unique' => 0];

        if (!empty($data)) {
            $item = $data[0]; // At account level, there's usually just one row for the range
            $summary['reach'] = (int) ($item['reach'] ?? 0);
            $summary['impressions'] = (int) ($item['impressions'] ?? 0);
            $summary['spend'] = (float) ($item['spend'] ?? 0);
            $summary['results'] = $this->findResultsValue($item['actions'] ?? []);
            $summary['link_click_unique'] = $this->getActionValue($item['unique_actions'] ?? [], 'link_click');
            
            if ($summary['reach'] > 0) {
                $summary['frequency'] = $summary['impressions'] / $summary['reach'];
            }
        }

        return $summary;
    }

    /**
     * Fetch Daily Account-Level Insights for a range
     * This is the "Absolute Truth" for daily totals
     */
    public function fetchDailyAccountInsights(string $adAccountId, array $range)
    {
        if (!$this->accessToken) return [];

        $params = [
            'access_token' => $this->accessToken,
            'level' => 'account',
            'time_increment' => 1,
            'fields' => 'spend',
            'time_range' => json_encode($range),
            'limit' => 1000
        ];

        $res = Http::timeout(60)->get("{$this->baseUrl}/{$adAccountId}/insights", $params);
        if ($res->failed()) return [];

        $data = $res->json()['data'] ?? [];
        $dailyTotals = [];

        foreach ($data as $item) {
            $date = $item['date_start'];
            $dailyTotals[$date] = (float) ($item['spend'] ?? 0);
        }

        return $dailyTotals;
    }
}
