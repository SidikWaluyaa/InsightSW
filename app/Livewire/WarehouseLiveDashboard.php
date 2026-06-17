<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Dashboard Gudang Live')]
class WarehouseLiveDashboard extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    
    // Tracking active queries
    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public bool $isLoaded = false;
    public ?string $errorMessage = null;
    
    // Cache TTL in seconds (10 seconds to keep it almost real-time while maintaining snappy search/pagination)
    protected int $cacheTtl = 10;



    public function mount()
    {
        $this->startDate = request()->query('startDate') ?? '';
        $this->endDate = request()->query('endDate') ?? '';

        // Default to last 8 days (7 days ago to today) to match the trend chart length of 8 labels
        if (empty($this->startDate)) {
            $this->startDate = now()->subDays(7)->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function loadData()
    {
        $this->isLoaded = true;
    }



    public function refreshData()
    {
        $cacheKey = "warehouse_live_dashboard_{$this->startDate}_{$this->endDate}";
        Cache::forget($cacheKey);
        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data gudang terbaru dari API.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    /**
     * Fetch warehouse summary data from API with cache.
     */
    protected function getWarehouseData(): array
    {
        try {
            $cacheKey = "warehouse_live_dashboard_{$this->startDate}_{$this->endDate}";
            
            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchSummary($this->startDate, $this->endDate, true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Warehouse Live Component Error: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        $summary = [];
        $qcAnalytics = [];
        $efficiency = [];
        $inventory = [];
        $storage = [];
        $queues = [];
        $period = [];
        $metadata = [];
        
        if ($this->isLoaded) {
            $apiData = $this->getWarehouseData();
            if (!empty($apiData)) {
                $summary = $apiData['summary'] ?? [];
                $qcAnalytics = $apiData['qc_analytics'] ?? [];
                $efficiency = $apiData['efficiency'] ?? [];
                $inventory = $apiData['inventory'] ?? [];
                $storage = $apiData['storage'] ?? [];
                $queues = $apiData['queues'] ?? [];
                $period = $apiData['period'] ?? [];
                $metadata = $apiData['metadata'] ?? [];
            }
        }

        return view('livewire.warehouse-live-dashboard', [
            'summary' => $summary,
            'qcAnalytics' => $qcAnalytics,
            'efficiency' => $efficiency,
            'inventory' => $inventory,
            'storage' => $storage,
            'queues' => $queues,
            'period' => $period,
            'metadata' => $metadata,
        ]);
    }
}
