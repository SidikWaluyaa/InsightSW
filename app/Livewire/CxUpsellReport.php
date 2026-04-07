<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use App\Services\CxService;
use App\Services\SyncService;
use Carbon\Carbon;

class CxUpsellReport extends Component
{
    #[Layout('layouts.app')]
    public $startDate;
    public $endDate;
    public $summary = []; // Tambah Jasa Summary
    public $otoSummary = []; // OTO Summary
    public $kpi = [];
    public $upsellItems = []; // Tambah Jasa Items
    public $otoItems = []; // OTO Items
    public $categories = []; // Tambah Jasa Categories
    public $otoCategories = []; // OTO Categories
    public $isLoading = false;
    public $errorMessage = null;
    public $activePreset = 'bulan_ini';
    public $lastSyncTimestamp;
    public $isSyncing = false;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        
        $this->updateSyncState();
        $this->checkSync(); // Force check on mount
        $this->fetchData();
    }

    /**
     * Update the sync timestamps from Cache for the UI to use.
     */
    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(SyncService::class)->getLastSyncTime('cx_upsell');
    }

    /**
     * Method triggered by wire:poll every 10s.
     */
    public function checkSync()
    {
        $this->isSyncing = true;
        
        $synced = app(SyncService::class)->syncIfAllowed('cx_upsell', function() {
            app(CxService::class)->fetchAndCache($this->startDate, $this->endDate, true);
        }, 60);

        if ($synced) {
            $this->fetchData(); // Refresh local properties from cache
        }

        $this->updateSyncState();
        $this->isSyncing = false;
    }

    public function setPreset($preset)
    {
        $this->activePreset = $preset;
        $now = Carbon::now();

        switch ($preset) {
            case 'hari_ini':
                $this->startDate = $now->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'kemarin':
                $this->startDate = $now->subDay()->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case '7_hari':
                $this->startDate = $now->subDays(6)->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case '30_hari':
                $this->startDate = $now->subDays(29)->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'bulan_ini':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'bulan_lalu':
                $this->startDate = $now->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'kuartal_ini':
                $this->startDate = $now->firstOfQuarter()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'tahun_ini':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
        }

        $this->fetchData();
    }

    public function fetchData($forceRefresh = false)
    {
        $this->isLoading = true;
        $this->resetError();

        try {
            $cxService = app(CxService::class);
            
            // Get data - if forceRefresh is true, we hit API.
            // Otherwise, we try to get from cache first.
            $data = $cxService->getCachedData($this->startDate, $this->endDate);

            if (!$data || $forceRefresh) {
                $data = $cxService->fetchAndCache($this->startDate, $this->endDate, $forceRefresh);
                $this->updateSyncState();
            }

            if ($data) {
                $result = data_get($data, 'result', $data);
                
                // 1. KPI Stats
                $this->kpi = (array)data_get($result, 'data.kpi', [
                    'total' => 0,
                    'open' => 0,
                    'resolved' => 0,
                    'cancelled' => 0,
                    'resolution_rate' => 0,
                    'avg_response_time_hours' => 0
                ]);

                // 2. MAPPING DATA UPSELL
                $upsellData = data_get($result, 'data.upsell', []);

                $this->upsellItems = (array)data_get($upsellData, 'tambah_jasa_items', []);
                $this->summary = [
                    'total_nominal' => (float)data_get($upsellData, 'total_nominal', 0),
                    'total_volume' => (int)data_get($upsellData, 'total_volume', 0),
                    'combined_arpu' => (float)data_get($upsellData, 'arpu_tambah_jasa', 0),
                ];

                $this->otoItems = (array)data_get($upsellData, 'oto_items', []);
                $this->otoSummary = [
                    'total_nominal' => (float)data_get($upsellData, 'oto_nominal', 0),
                    'total_volume' => (int)data_get($upsellData, 'oto_volume', 0),
                    'combined_arpu' => (float)data_get($upsellData, 'arpu_oto', 0),
                ];

                // Standardize categories for Chart
                $standardize = function($items) {
                    return collect($items)->map(function($i) {
                        $cat = data_get($i, 'category_name');
                        $i['category_name'] = !empty($cat) ? $cat : 'Lainnya';
                        return $i;
                    });
                };

                $cleanUpsell = $standardize($this->upsellItems);
                $cleanOto = $standardize($this->otoItems);

                $this->categories = $cleanUpsell->groupBy('category_name')->map(fn($g) => count($g))->toArray();
                $this->otoCategories = $cleanOto->groupBy('category_name')->map(fn($g) => count($g))->toArray();

                // Dispatch for Chart
                $allLabels = $cleanUpsell->concat($cleanOto)->pluck('category_name')->unique()->values()->toArray();

                $this->dispatch('analytics-updated', [
                    'barChartData' => [
                        'labels' => $allLabels,
                        'jasaRev' => $cleanUpsell->groupBy('category_name')->map(fn($g) => $g->sum('total_revenue'))->toArray(),
                        'otoRev' => $cleanOto->groupBy('category_name')->map(fn($g) => $g->sum('total_revenue'))->toArray(),
                    ],
                    'donutChartData' => [
                        'labels' => array_keys(array_merge($this->categories, $this->otoCategories)),
                        'counts' => collect($allLabels)->map(function($label) {
                            return ($this->categories[$label] ?? 0) + ($this->otoCategories[$label] ?? 0);
                        })->toArray(),
                    ]
                ]);
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Data Gagal Dimuat: ' . $e->getMessage();
        }

        $this->isLoading = false;
    }

    public function refreshData()
    {
        $this->fetchData(true);
    }


    public function applyFilter()
    {
        $this->fetchData();
    }

    private function handleError($response)
    {
        $this->errorMessage = 'API Error (' . $response->status() . '): ' . ($response->json()['message'] ?? 'Gagal mengambil data');
    }

    private function resetError()
    {
        $this->errorMessage = null;
    }

    public function render()
    {
        return view('livewire.cx-upsell-report');
    }
}
