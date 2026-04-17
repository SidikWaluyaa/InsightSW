<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseSortir;
use App\Models\WarehouseForecast;
use App\Services\WarehouseSyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit & Prediksi Intelijen')]
class WarehouseIntelligence extends Component
{
    use WithPagination;

    public $isLoading = false;
    public $readinessData = [];
    public $urgentProcurement = [];
    public $summaryStats = [];

    public function mount()
    {
        $this->loadIntelligence();
    }

    public function loadIntelligence()
    {
        // 1. Production Readiness Breakdown
        $sortirData = WarehouseSortir::all();
        $total = $sortirData->count() ?: 1;
        
        $this->readinessData = [
            'siap_pct' => ($sortirData->where('sortir_category', 'SIAP PRODUKSI')->count() / $total) * 100,
            'siap_count' => $sortirData->where('sortir_category', 'SIAP PRODUKSI')->count(),
            'procurement_pct' => ($sortirData->where('sortir_category', 'IN PROCUREMENT')->count() / $total) * 100,
            'procurement_count' => $sortirData->where('sortir_category', 'IN PROCUREMENT')->count(),
            'request_pct' => ($sortirData->where('sortir_category', 'BELUM REQUEST')->count() / $total) * 100,
            'request_count' => $sortirData->where('sortir_category', 'BELUM REQUEST')->count(),
            'total_count' => $sortirData->count()
        ];

        // 2. Urgent Procurement (Stock-Gap Analysis)
        $this->urgentProcurement = WarehouseForecast::where('forecast_remaining', '<=', 0)
            ->orderBy('forecast_remaining', 'asc')
            ->get();

        // 3. Summary Stats
        $this->summaryStats = [
            'total_valuation' => \App\Models\WarehouseInventory::sum('total_valuation'),
            'critical_items' => $this->urgentProcurement->count(),
            'sla_violations' => WarehouseSortir::where('is_sla_violated', true)->orWhere('days_in_sortir', '>', 3)->count()
        ];
    }

    public function syncIntelligence()
    {
        $this->isLoading = true;
        
        try {
            $syncService = new WarehouseSyncService();
            $syncService->syncSortir();
            $syncService->syncForecast();
            $syncService->syncInventory(); // Refresh valuation

            $this->loadIntelligence();

            $this->dispatch('swal', [
                'title' => 'Audit Berhasil',
                'text' => 'Data intelijen terbaru telah disinkronkan.',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Audit Gagal',
                'text' => $e->getMessage(),
                'icon' => 'error'
            ]);
        }

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.warehouse-intelligence', [
            'bottlenecks' => WarehouseSortir::where('is_sla_violated', true)
                ->orWhere('days_in_sortir', '>', 3)
                ->orderBy('days_in_sortir', 'desc')
                ->paginate(10)
        ]);
    }
}
