<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WarehouseApiService;
use App\Services\WarehouseSyncService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Warehouse Command Center')]
class WarehouseCommandCenter extends Component
{
    public $summary = [];
    public $rackMap = [];
    public $qcAnalytics = [];
    public $lastSync = null;
    public $isLoading = false;
    public $inventoryCount = 0;
    public $requestCount = 0;
    public $transactionCount = 0;
    public $syncResults = [];

    // Date Filters for Shoe Metrics
    public $startDate = '';
    public $endDate = '';

    public function mount()
    {
        $this->startDate = date('Y-m-01'); // Mulai dari tanggal 1 bulan ini
        $this->endDate = date('Y-m-d');   // Sampai hari ini
        $this->loadData();
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $this->inventoryCount = \App\Models\WarehouseInventory::count();
        $this->requestCount = \App\Models\WarehouseRequest::count();
        $this->transactionCount = \App\Models\WarehouseTransaction::count();
    }

    public function updatedStartDate() { $this->loadData(); }
    public function updatedEndDate() { $this->loadData(); }

    public function loadData($forceRefresh = false)
    {
        $this->isLoading = true;
        
        try {
            $apiService = new WarehouseApiService();
            $start = $this->startDate ?: now()->subDays(30)->toDateString();
            $end = $this->endDate ?: now()->toDateString();
            
            // Kita tambahkan parameter force_refresh jika diperlukan
            $data = $apiService->fetchSummary($start, $end, $forceRefresh);
            
            $this->summary = $data['summary'] ?? []; // Menggunakan 'summary' karena di Resource (Server) di-rename dari metrics
            $this->rackMap = $data['storage']['heatmap'] ?? [];
            $this->qcAnalytics = $data['qc_analytics'] ?? [];
            $this->lastSync = now()->format('H:i:s');
        } catch (\Exception $e) {
            Log::error("Command Center Load Error: " . $e->getMessage());
            $this->dispatch('swal', [
                'title' => 'Error Load Data',
                'text' => 'Gagal mengambil data operasional. Menggunakan data terakhir.',
                'icon' => 'warning'
            ]);
        }
        
        $this->isLoading = false;
    }

    public function syncNow()
    {
        $this->isLoading = true;
        
        try {
            $syncService = new WarehouseSyncService();
            
            $resInv = $syncService->syncInventory();
            $resReq = $syncService->syncRequests();
            $resTrx = $syncService->syncTransactions();
            $resSortir = $syncService->syncSortir();
            $resForecast = $syncService->syncForecast();
            
            $this->updateCounts();
            $this->loadData(true); // Paksa refresh cache API

            $this->dispatch('swal', [
                'title' => 'Tarik Data Berhasil',
                'text' => 'Data operasional telah diperbarui dari server.',
                'icon' => 'success',
                'timer' => 3000
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Sync Gagal',
                'text' => $e->getMessage(),
                'icon' => 'error'
            ]);
        }

        $this->isLoading = false;
    }

    public function setRange($range)
    {
        switch ($range) {
            case 'today':
                $this->startDate = now()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case '7days':
                $this->startDate = now()->subDays(7)->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case '30days':
                $this->startDate = now()->subDays(30)->toDateString();
                $this->endDate = now()->toDateString();
                break;
        }
        $this->loadData(true);
    }

    #[Computed]
    public function warehouseStats()
    {
        // Pemetaan Key yang benar dari WarehouseDashboardApiService
        return [
            'total_sepatu_dirak' => $this->summary['stored_items'] ?? 0,
            'total_sepatu_finish_periode' => $this->summary['finished_day'] ?? 0,
            'total_sepatu_diterima_periode' => $this->summary['incoming_day'] ?? 0,
            'total_spk_print' => $this->summary['spk_print'] ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.warehouse-command-center');
    }
}
