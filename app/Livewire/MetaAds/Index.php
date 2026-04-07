<?php

namespace App\Livewire\MetaAds;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MetaAdsReport;
use App\Services\MetaAdsService;
use App\Services\SyncService;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $selectedCampaign = '';

    #[Url(except: '')]
    public string $selectedAdset = '';

    #[Url(except: '')]
    public string $startDate = '';

    #[Url(except: '')]
    public string $endDate = '';

    #[Url(except: 'campaign')]
    public string $groupBy = 'campaign';

    public array $selectedColumns = [
        'status', 
        'results', 
        'reach', 
        'frequency', 
        'cost_per_result', 
        'budget', 
        'spend', 
        'stop_time', 
        'impressions', 
        'cpm', 
        'link_click', 
        'cpc', 
        'ctr', 
        'clicks_all', 
        'ctr_all', 
        'cpc_all'
    ];
    public array $metaSummary = [];
    public array $campaignSpend = []; // New breakdown data
    public $adsets = [];

    // Detail Panel State
    public bool $showDetail = false;
    public ?array $viewingData = null;

    public $lastSyncTimestamp;
    public $isSyncing = false;

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
    }

    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(SyncService::class)->getLastSyncTime('meta_ads_sync');
    }

    public function checkSync()
    {
        // Auto sync only for today
        if ($this->startDate !== now()->format('Y-m-d')) {
            return;
        }

        $this->isSyncing = true;
        app(SyncService::class)->syncIfAllowed('meta_ads_sync', function() {
            $adAccountId = 'act_1922369221497688';
            app(MetaAdsService::class)->fetchAndSync($adAccountId, 'today');
        }, 60);

        $this->updateSyncState();
        $this->loadSummary();
        $this->isSyncing = false;
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'selectedCampaign', 'selectedAdset', 'startDate', 'endDate', 'groupBy'])) {
            $this->resetPage();
        }
    }

    public function selectCampaign($campaignName)
    {
        $this->selectedCampaign = $campaignName;
        $this->selectedAdset = '';
        $this->groupBy = 'adset'; // Auto-drill down to Adsets
        $this->resetPage();
    }

    public function selectAdset($adsetName)
    {
        $this->selectedAdset = $adsetName;
        $this->groupBy = 'none'; // Auto-drill down to individual Ads
        $this->resetPage();
    }

    public function resetNavigation()
    {
        $this->selectedCampaign = '';
        $this->selectedAdset = '';
        $this->groupBy = 'campaign';
        $this->resetPage();
    }

    public function toggleColumn($column)
    {
        if (in_array($column, $this->selectedColumns)) {
            $this->selectedColumns = array_diff($this->selectedColumns, [$column]);
        } else {
            $this->selectedColumns[] = $column;
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedCampaign', 'selectedAdset', 'startDate', 'endDate']);
        $this->groupBy = 'campaign';
        $this->resetPage();
    }

    /**
     * Synchronize statistics with Meta API
     */
    public function sync(MetaAdsService $service)
    {
        $adAccountId = 'act_1922369221497688';
        $this->isSyncing = true;
        
        try {
            // Sync for the selected start date or default to today
            $syncDate = $this->startDate ?: 'today';
            $result = $service->fetchAndSync($adAccountId, $syncDate);

            if ($result) {
                // Update timestamp manually since we forced it
                \Illuminate\Support\Facades\Cache::put("sync_last_time_meta_ads_sync", time(), now()->addDays(1));
                $this->updateSyncState();

                $this->loadSummary();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Sync Success',
                    'text' => 'Data Meta Ads untuk tanggal ' . $syncDate . ' telah diperbarui.',
                ]);
            } else {
                throw new \Exception("Sync process failed at service level.");
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Sync Error',
                'text' => 'Gagal sinkronisasi: ' . $e->getMessage(),
            ]);
        }

        $this->isSyncing = false;
    }

    public function showDetail($id)
    {
        $report = MetaAdsReport::find($id);
        if ($report) {
            $this->viewingData = $report->toArray();
            $this->viewingData['is_aggregated'] = $this->groupBy !== 'none';
            $this->viewingData['is_precision'] = true;
            $this->showDetail = true;
        }
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->viewingData = null;
    }

    public function loadSummary()
    {
        $query = MetaAdsReport::query();

        if ($this->startDate) {
            $query->where('date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->where('date', '<=', $this->endDate);
        }
        if ($this->selectedCampaign) {
            $query->where('campaign_name', $this->selectedCampaign);
        }
        if ($this->selectedAdset) {
            $query->where('adset_name', $this->selectedAdset);
        }
        if ($this->search) {
            $query->where(function($q) {
                $q->where('ad_name', 'like', '%' . $this->search . '%')
                  ->orWhere('campaign_name', 'like', '%' . $this->search . '%')
                  ->orWhere('adset_name', 'like', '%' . $this->search . '%');
            });
        }

        // Atomically fetch all sums in ONE query for 100% precision
        $totals = (clone $query)->selectRaw('
            SUM(spend) as total_spend, 
            SUM(results) as total_results, 
            SUM(reach) as total_reach, 
            SUM(impressions) as total_impressions
        ')->first();

        // Standardize variables for math operations
        $finalSpend = (float) ($totals->total_spend ?? 0);
        $finalResults = (int) ($totals->total_results ?? 0);
        $finalReach = (int) ($totals->total_reach ?? 0);
        $finalImpressions = (int) ($totals->total_impressions ?? 0);

        $this->metaSummary = [
            'total_spend' => $finalSpend,
            'total_reach' => $finalReach,
            'total_impressions' => $finalImpressions,
            'total_results' => $finalResults,
            'avg_frequency' => ($finalReach > 0) ? ($finalImpressions / $finalReach) : 0,
            'avg_cost_per_result' => ($finalResults > 0) ? ($finalSpend / $finalResults) : 0,
        ];

        // Fetch Campaign Spend Breakdown
        $this->campaignSpend = MetaAdsReport::query()
            ->when($this->startDate, fn($q) => $q->where('date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->where('date', '<=', $this->endDate))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('ad_name', 'like', '%' . $this->search . '%')
                        ->orWhere('campaign_name', 'like', '%' . $this->search . '%')
                        ->orWhere('adset_name', 'like', '%' . $this->search . '%');
                });
            })
            ->selectRaw('campaign_name, SUM(spend) as total_spend')
            ->groupBy('campaign_name')
            ->orderBy('total_spend', 'desc')
            ->get()
            ->toArray();

        // Ensure adsets are filtered based on selected campaign
        $this->adsets = MetaAdsReport::query()
            ->when($this->selectedCampaign, fn($q) => $q->where('campaign_name', $this->selectedCampaign))
            ->distinct()
            ->pluck('adset_name')
            ->toArray();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->loadSummary();

        $query = MetaAdsReport::query();

        $query->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('ad_name', 'like', '%' . $this->search . '%')
                    ->orWhere('campaign_name', 'like', '%' . $this->search . '%')
                    ->orWhere('adset_name', 'like', '%' . $this->search . '%');
            });
        });

        $query->when($this->selectedCampaign, fn($q) => $q->where('campaign_name', $this->selectedCampaign));
        $query->when($this->selectedAdset, fn($q) => $q->where('adset_name', $this->selectedAdset));
        $query->when($this->startDate, fn($q) => $q->where('date', '>=', $this->startDate));
        $query->when($this->endDate, fn($q) => $q->where('date', '<=', $this->endDate));

        if ($this->groupBy !== 'none') {
            $groupByColumn = 'campaign_name';
            if ($this->groupBy === 'date') $groupByColumn = 'date';
            if ($this->groupBy === 'adset') $groupByColumn = 'adset_name';
            
            $selectFields = [
                "MAX(id) as id",
                $groupByColumn,
                "SUM(impressions) as impressions",
                "SUM(reach) as reach",
                "SUM(clicks) as clicks",
                "SUM(spend) as spend",
                "SUM(results) as results",
                "SUM(video_p25) as video_p25",
                "SUM(video_p50) as video_p50",
                "SUM(video_p75) as video_p75",
                "SUM(video_p100) as video_p100",
                "SUM(link_click_unique) as link_click_unique",
                "SUM(link_click) as link_click",
                "(SUM(spend) / NULLIF(SUM(results), 0)) as cost_per_result",
                "(SUM(link_click) / NULLIF(SUM(impressions), 0) * 100) as ctr",
                "(SUM(spend) / NULLIF(SUM(link_click), 0)) as cpc",
                "(SUM(spend) / NULLIF(SUM(impressions), 0) * 1000) as cpm",
                "SUM(impressions) / NULLIF(SUM(reach), 0) as frequency",
                "SUM(clicks_all) as clicks_all",
                "(SUM(clicks_all) / NULLIF(SUM(impressions), 0) * 100) as ctr_all",
                "(SUM(spend) / NULLIF(SUM(clicks_all), 0)) as cpc_all",
                "MAX(budget) as budget",
                "COALESCE(MAX(CASE WHEN status = 'ACTIVE' THEN 'ACTIVE' END), MAX(status)) as status",
                "MAX(stop_time) as stop_time"
            ];

            if ($this->groupBy === 'adset') {
                $selectFields[] = "MAX(campaign_name) as campaign_name";
            }
            if ($this->groupBy !== 'date') {
                $selectFields[] = "MAX(date) as date";
            }

            $query->selectRaw(implode(', ', $selectFields))->groupBy($groupByColumn);
        }

        $reports = $query->orderBy($this->groupBy === 'campaign' ? 'spend' : 'date', 'desc')->paginate(15);
        $campaigns = MetaAdsReport::distinct()->pluck('campaign_name');
        
        return view('livewire.meta-ads.index', compact('reports', 'campaigns'));
    }
}
