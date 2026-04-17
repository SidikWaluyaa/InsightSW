<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WarehouseApiService;
use App\Services\WarehouseSyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;

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

    public function mount()
    {
        $this->loadData();
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $this->inventoryCount = \App\Models\WarehouseInventory::count();
        $this->requestCount = \App\Models\WarehouseRequest::count();
        $this->transactionCount = \App\Models\WarehouseTransaction::count();
    }

    public function loadData()
    {
        $this->isLoading = true;
        
        try {
            $apiService = new WarehouseApiService();
            $data = $apiService->fetchSummary(now()->subDays(30)->toDateString(), now()->toDateString());
            
            $this->summary = $data['summary'] ?? [];
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
            $this->loadData();

            $msg = sprintf(
                "Inventori: %d items\nRequest: %d items\nTransaksi: %d items",
                $resInv['count'] ?? 0,
                $resReq['count'] ?? 0,
                $resTrx['count'] ?? 0
            );

            $this->dispatch('swal', [
                'title' => 'Tarik Data Berhasil',
                'text' => $msg,
                'icon' => 'success',
                'timer' => 5000
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

    public function render()
    {
        return view('livewire.warehouse-command-center');
    }
}
