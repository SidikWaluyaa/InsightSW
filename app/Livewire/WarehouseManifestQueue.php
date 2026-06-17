<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Antrian Manifest Live')]
class WarehouseManifestQueue extends Component
{
    public string $startDate = '';
    public string $endDate = '';

    // Tracking active queries in URL
    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public bool $isLoaded = false;
    public ?string $errorMessage = null;

    // Cache TTL in seconds (10 seconds)
    protected int $cacheTtl = 10;

    public function mount()
    {
        $this->startDate = request()->query('startDate') ?? '';
        $this->endDate = request()->query('endDate') ?? '';

        // Default to last 8 days (7 days ago to today) to display a nice chart trend
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
        $cacheKey = "warehouse_manifest_summary_{$this->startDate}_{$this->endDate}";
        Cache::forget($cacheKey);
        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data manifest terbaru dari API.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    /**
     * Fetch manifest data from API.
     */
    protected function getManifestData(): array
    {
        try {
            $cacheKey = "warehouse_manifest_summary_{$this->startDate}_{$this->endDate}";

            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchManifestSummary($this->startDate, $this->endDate, true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Warehouse Manifest Component Error: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        $summary = [];
        $dailyTrends = [];
        $recentManifests = [];
        $period = [];
        $metadata = [];

        if ($this->isLoaded) {
            $apiData = $this->getManifestData();
            if (!empty($apiData)) {
                $summary = $apiData['summary'] ?? [];
                $dailyTrends = $apiData['daily_trends'] ?? [];
                $recentManifests = $apiData['recent_manifests'] ?? [];
                $period = $apiData['period'] ?? [];
                $metadata = $apiData['metadata'] ?? [];
            }
        }

        return view('livewire.warehouse-manifest-queue', [
            'summary' => $summary,
            'dailyTrends' => $dailyTrends,
            'recentManifests' => $recentManifests,
            'period' => $period,
            'metadata' => $metadata,
        ]);
    }
}
