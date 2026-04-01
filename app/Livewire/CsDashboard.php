<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DashboardApiService;
use App\Services\SleekflowService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CsDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $lastSynced;
    public $isLoading = false;

    // Data from Sleekflow (Chat Analytics)
    public $sleekflowMetrics = [];
    
    // Data from External API (Operational Analytics)
    public $apiSummary = [];
    public $perCs = [];
    
    public $errorMessage = null;

    protected $queryString = ['startDate', 'endDate'];

    public function mount()
    {
        // Default to today to avoid heavy initial load
        $this->startDate = $this->startDate ?: Carbon::now()->format('Y-m-d');
        $this->endDate = $this->endDate ?: Carbon::now()->format('Y-m-d');
        $this->lastSynced = Carbon::now();
        
        $this->loadData();
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
            // 1. Fetch from Sleekflow (Chat Analytics)
            $sleekflowService = app(SleekflowService::class);
            $this->sleekflowMetrics = $sleekflowService->getAnalyticsData($this->startDate, $this->endDate);

            // 2. Fetch from External API (Operational Analytics)
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
                    
                    $totalLeads = collect($this->perCs)->sum('total_leads'); // assuming leads might be there
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

        $this->lastSynced = Carbon::now();
        $this->isLoading = false;
    }

    /**
     * Polling for real-time updates
     */
    public function autoRefresh()
    {
        // Only auto refresh for today's context
        if ($this->endDate === Carbon::now()->format('Y-m-d')) {
            $this->loadData();
        }
    }

    /**
     * Manual sync trigger
     */
    public function refreshManually()
    {
        // Sync Sleekflow if today
        if ($this->endDate === Carbon::now()->format('Y-m-d')) {
            app(SleekflowService::class)->syncContacts($this->startDate, $this->endDate);
        }

        $this->loadData(true); // force_refresh = true for external API
        
        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Seluruh data chat dan operasional berhasil disinkronkan.',
            'icon' => 'success',
            'toast' => true,
            'position' => 'top-end'
        ]);
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
