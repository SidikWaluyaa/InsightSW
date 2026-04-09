<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WarehouseApiService;
use Carbon\Carbon;
use Exception;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WarehouseDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $data = [];
    public $searchQuery = '';
    public $activeRackTab = 'FINISH';

    public function setRackTab($tab)
    {
        $this->activeRackTab = $tab;
    }
    public $error = null;
    public $lastUpdated;
    public $isLoading = false;

    protected $warehouseService;

    public function boot(WarehouseApiService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function mount()
    {
        // Default to today
        $this->startDate = Carbon::now()->startOfDay()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        try {
            $this->data = $this->warehouseService->fetchSummary($this->startDate, $this->endDate);
            $this->lastUpdated = Carbon::now()->format('H:i:s');
            $this->error = null;
            $this->dispatch('dataUpdated', $this->data);
        } catch (Exception $e) {
            $this->error = "API Error: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function refresh()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.warehouse-dashboard');
    }
}
