<?php

namespace App\Livewire\MetaAds;

use App\Livewire\Concerns\WithDateRange;
use App\Services\ManagementMetricsService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
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
    use WithDateRange;

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
    public $lastGlobalSyncTrigger = 0;

    public function mount()
    {
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: now()->startOfMonth()->toDateString(),
            $this->endDate ?: now()->toDateString()
        );
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
    }

    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(SyncService::class)->getLastSyncTime('meta_ads_sync');
    }

    public function checkSync()
    {
        // 1. Detect if a global sync happened in another tab (Workshop Dashboard, etc.)
        $globalSyncTrigger = (int) \Illuminate\Support\Facades\Cache::get('global_sync_trigger', 0);
        if ($globalSyncTrigger > $this->lastGlobalSyncTrigger) {
            $this->loadSummary();
            $this->lastGlobalSyncTrigger = $globalSyncTrigger;
            $this->updateSyncState();
        }

        // 2. Auto sync hari ini di background, hanya kalau rentang mencakup hari ini
        if ($this->endDate !== now()->format('Y-m-d')) {
            return;
        }

        $this->isSyncing = true;
        app(SyncService::class)->syncIfAllowed('meta_ads_sync', function() {
            $adAccountId = 'act_1922369221497688';
            app(MetaAdsService::class)->fetchAndSync($adAccountId, 'today');
        }, 120);

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

    protected function dateRangeChanged(): void
    {
        $this->resetPage();
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
        $this->reset(['search', 'selectedCampaign', 'selectedAdset']);
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
            // Meta menolak rentang panjang di level iklan ("Service temporarily unavailable"), jadi ditarik per 7 hari
            $syncLabel = $this->startDate . ' s/d ' . $this->endDate;
            $result = true;
            $cursor = Carbon::parse($this->startDate);
            $last = Carbon::parse($this->endDate);

            while ($result && $cursor->lte($last)) {
                $chunkEnd = $cursor->copy()->addDays(6)->min($last);
                $result = $service->fetchAndSync($adAccountId, ['since' => $cursor->toDateString(), 'until' => $chunkEnd->toDateString()]);
                $cursor = $chunkEnd->addDay();
            }

            if ($result) {
                // Update timestamp manually since we forced it
                \Illuminate\Support\Facades\Cache::put("sync_last_time_meta_ads_sync", time(), now()->addDays(1));
                $this->updateSyncState();

                $this->loadSummary();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Sync Success',
                    'text' => 'Data Meta Ads untuk periode ' . $syncLabel . ' telah diperbarui.',
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

    /**
     * 4 kartu efisiensi iklan (PRD halaman 4) dari tabel meta_ads_reports, mengikuti filter kampanye/adset/cari,
     * dengan lampu dibanding periode yang sama bulan lalu (aturan MoM sama dengan Data Manajemen SW).
     */
    protected function kpis(ManagementMetricsService $service): array
    {
        $totals = function (string $start, string $end) {
            $row = $this->filteredQuery($start, $end)
                ->selectRaw('SUM(spend) spend, SUM(results) results, SUM(link_click) link_click, SUM(impressions) impressions')
                ->first();
            $spend = (float) $row->spend;
            $results = (float) $row->results;
            $impressions = (float) $row->impressions;

            return [
                'spend' => $spend,
                'results' => $results,
                'cpr' => $results > 0 ? $spend / $results : null,
                'ctr' => $impressions > 0 ? $row->link_click / $impressions * 100 : null,
            ];
        };

        [$prevStart, $prevEnd] = $service->previousRange($this->startDate, $this->endDate);
        $now = $totals($this->startDate, $this->endDate);
        $prev = $totals($prevStart, $prevEnd);
        $days = $service->days($this->startDate, $this->endDate);
        $prevDays = $service->days($prevStart, $prevEnd);

        // [arah, dibanding per hari]: arah null = tanpa lampu (biaya iklan bukan baik/buruk)
        $rules = ['spend' => [null, true], 'results' => ['naik', true], 'cpr' => ['turun', false], 'ctr' => ['naik', false]];
        $cards = [];
        foreach ($rules as $key => [$arah, $perDay]) {
            $cards[$key] = ['nilai' => $now[$key], 'lalu' => $prev[$key]]
                + $service->compareValues($now[$key], $prev[$key] ?: null, $arah, $perDay, $days, $prevDays);
        }

        return ['cards' => $cards, 'days' => $days, 'prevRange' => [$prevStart, $prevEnd]];
    }

    /** Query meta_ads_reports dengan filter halaman (kampanye, adset, cari) untuk rentang tertentu. */
    protected function filteredQuery(string $start, string $end)
    {
        return MetaAdsReport::query()
            ->whereBetween('date', [$start, $end])
            ->when($this->selectedCampaign, fn ($q) => $q->where('campaign_name', $this->selectedCampaign))
            ->when($this->selectedAdset, fn ($q) => $q->where('adset_name', $this->selectedAdset))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('ad_name', 'like', '%' . $this->search . '%')
                        ->orWhere('campaign_name', 'like', '%' . $this->search . '%')
                        ->orWhere('adset_name', 'like', '%' . $this->search . '%');
                });
            });
    }

    /**
     * Tabel berjenjang PRD: Tanggal → Materi iklan → Kampanye. Dipaginasi per tanggal (7 tanggal per halaman)
     * supaya satu tanggal tidak terpotong; sel tanggal & materi iklan yang berulang dikosongkan di view.
     */
    protected function hierarchy(): LengthAwarePaginator
    {
        $perPage = 7;
        $dates = $this->filteredQuery($this->startDate, $this->endDate)->distinct()->orderByDesc('date')->pluck('date');
        $page = $this->getPage();
        $pageDates = $dates->forPage($page, $perPage)->values();

        $rows = $pageDates->isEmpty() ? collect() : $this->filteredQuery($this->startDate, $this->endDate)
            ->whereIn('date', $pageDates)
            ->selectRaw('date, ad_name, campaign_name, SUM(spend) spend, SUM(impressions) impressions, SUM(link_click) link_click, SUM(results) results')
            ->groupBy('date', 'ad_name', 'campaign_name')
            // Iklan yang tidak tayang dan tidak menghasilkan chat di hari itu tidak ditampilkan
            ->havingRaw('SUM(spend) > 0 OR SUM(results) > 0')
            ->get()
            // Urutan: tanggal terbaru, lalu materi dengan biaya terbesar, lalu kampanye dengan biaya terbesar
            ->groupBy(fn ($r) => Carbon::parse($r->date)->toDateString())
            ->sortKeysDesc()
            ->map(fn ($day) => $day->groupBy('ad_name')
                ->sortByDesc(fn ($ads) => $ads->sum('spend'))
                ->map(fn ($ads) => $ads->sortByDesc('spend')->values()));

        return new LengthAwarePaginator($rows, $dates->count(), $perPage, $page, ['path' => request()->url(), 'pageName' => 'page']);
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

        if (! in_array($this->groupBy, ['none', 'materi'], true)) {
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

        $campaigns = MetaAdsReport::distinct()->pluck('campaign_name');
        $service = app(ManagementMetricsService::class);
        $kpi = $this->kpis($service);
        $presets = $this->datePresets();
        $maxDate = $this->maxSelectableDate()->toDateString();

        if ($this->groupBy === 'materi') {
            $hierarchy = $this->hierarchy();

            return view('livewire.meta-ads.index', compact('hierarchy', 'campaigns', 'kpi', 'presets', 'maxDate') + ['reports' => null]);
        }

        $reports = $query->orderBy($this->groupBy === 'campaign' ? 'spend' : 'date', 'desc')->paginate(15);

        return view('livewire.meta-ads.index', compact('reports', 'campaigns', 'kpi', 'presets', 'maxDate') + ['hierarchy' => null]);
    }
}
