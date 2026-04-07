<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DashboardApiService;
use App\Services\SleekflowService;
use App\Services\SyncService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CsDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $isLoading = false;
    public $sleekflowMetrics = [];
    public $apiSummary = [];
    public $perCs = [];
    public $errorMessage = null;

    public $lastSyncChat;
    public $lastSyncOperational;
    public $isSyncing = false;

    protected $queryString = ['startDate', 'endDate'];

    public function mount()
    {
        // Default to today to avoid heavy initial load
        $this->startDate = $this->startDate ?: Carbon::now()->format('Y-m-d');
        $this->endDate = $this->endDate ?: Carbon::now()->format('Y-m-d');
        
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
        $this->loadData();
    }

    /**
     * Update the sync timestamps from Cache for the UI to use.
     */
    public function updateSyncState()
    {
        $syncService = app(SyncService::class);
        $this->lastSyncChat = $syncService->getLastSyncTime('chat_sync');
        $this->lastSyncOperational = $syncService->getLastSyncTime('operational_sync');
    }

    /**
     * Method triggered by wire:poll every 10s.
     */
    public function checkSync()
    {
        // Only auto refresh for today's context
        if ($this->endDate !== Carbon::now()->format('Y-m-d')) {
            return;
        }

        $this->isSyncing = true;
        $syncService = app(SyncService::class);

        // 1. Sync Chat (Sleekflow)
        $syncService->syncIfAllowed('chat_sync', function() {
            app(SleekflowService::class)->syncContacts($this->startDate, $this->endDate);
        }, 60);

        // 2. Sync Operational (Dashboard API)
        $syncService->syncIfAllowed('operational_sync', function() {
            app(DashboardApiService::class)->getDashboardSummary($this->startDate, $this->endDate, true);
        }, 60);

        $this->updateSyncState();
        $this->loadData();
        $this->isSyncing = false;
    }

    /**
     * Manual Filter Trigger
     */
    public function applyFilter()
    {
        $this->loadData();
    }

    /**
     * Fetch all data sources
     */
    public function loadData(bool $forceRefresh = false)
    {
        $this->isLoading = true;
        
        try {
            // 1. Fetch from Sleekflow (Chat Analytics) - Fast DB Query
            $sleekflowService = app(SleekflowService::class);
            $this->sleekflowMetrics = $sleekflowService->getAnalyticsData($this->startDate, $this->endDate);

            // 2. Fetch from External API (Operational Analytics) - Throttled/Cached
            $apiService = app(DashboardApiService::class);
            $result = $apiService->getDashboardSummary($this->startDate, $this->endDate, $forceRefresh);

            if (isset($result['status']) && $result['status'] === 'success') {
                $this->apiSummary = $result['data']['global'] ?? [];
                $this->perCs = $result['data']['per_cs'] ?? [];
                $this->errorMessage = null;

                // Robust Fallback: If global metrics are 0 but CS data exists, calculate from CS data
                if ((($this->apiSummary['revenue'] ?? 0) == 0) && !empty($this->perCs)) {
                    $this->apiSummary['revenue'] = collect($this->perCs)->sum('revenue');
                    $this->apiSummary['total_closing'] = collect($this->perCs)->sum('total_closing');
                    $this->apiSummary['total_sepatu_masuk'] = collect($this->perCs)->sum('total_sepatu_masuk');
                    $this->apiSummary['in_gudang'] = collect($this->perCs)->sum('in_gudang');
                    
                    $this->apiSummary['avg_deal'] = $this->apiSummary['total_closing'] > 0 
                        ? round($this->apiSummary['revenue'] / $this->apiSummary['total_closing']) 
                        : 0;
                    
                    if (!isset($this->apiSummary['kalkulasi_closing']) || $this->apiSummary['kalkulasi_closing'] == '0%') {
                        $this->apiSummary['kalkulasi_closing'] = 'Calculated from Agent Data';
                    }
                }
            } else {
                $this->errorMessage = $result['message'] ?? 'Gagal memuat data operasional dari API.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }

        $this->isLoading = false;
    }

    /**
     * Manual sync trigger
     */
    public function refreshManually()
    {
        $this->isSyncing = true;

        // Force both
        app(SleekflowService::class)->syncContacts($this->startDate, $this->endDate);
        $this->loadData(true); 

        // Update timestamps manually as we forced it
        $now = time();
        \Illuminate\Support\Facades\Cache::put("sync_last_time_chat_sync", $now, now()->addDays(1));
        \Illuminate\Support\Facades\Cache::put("sync_last_time_operational_sync", $now, now()->addDays(1));
        
        $this->updateSyncState();
        
        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Seluruh data chat dan operasional berhasil disinkronkan.',
            'icon' => 'success',
            'toast' => true,
            'position' => 'top-end'
        ]);

        $this->isSyncing = false;
    }

    #[On('set-date-filters')]
    public function setDateFilters($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.cs-dashboard');
    }
}
