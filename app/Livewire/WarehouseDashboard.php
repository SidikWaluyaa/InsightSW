<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseInventory;
use App\Models\WarehouseRequest;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseApiService;
use App\Services\WarehouseSyncService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class WarehouseDashboard extends Component
{
    use WithPagination;

    public $isSyncing = false;
    public $search = '';
    public $subCategoryFilter = 'all';
    public $statusFilter = 'all';

    // Date Filters
    public $startDate = '';
    public $endDate = '';
    public $lastSync = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSubCategoryFilter() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        $this->lastSync = now()->format('H:i:s');
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
        $this->syncAll();
    }

    public function syncAll()
    {
        $this->isSyncing = true;
        
        try {
            $service = new WarehouseSyncService();
            $service->syncInventory();
            $service->syncRequests();
            $service->syncTransactions();

            $this->lastSync = now()->format('H:i:s');
            
            $this->dispatch('swal', [
                'title' => 'Sync Berhasil',
                'text' => 'Data operasional Gudang telah terbarui.',
                'icon' => 'success',
                'timer' => 2000
            ]);
        } catch (\Exception $e) {
            Log::error("Dashboard Sync Error: " . $e->getMessage());
        }

        $this->isSyncing = false;
    }

    // ─── KPI METRICS (INVENTORY) ────────────────

    #[Computed]
    public function assetValuation()
    {
        $valuation = WarehouseInventory::select('category', DB::raw('SUM(total_valuation) as total'))
            ->groupBy('category')
            ->get();

        return [
            'grand_total' => $valuation->sum('total'),
            'categories' => $valuation
        ];
    }

    #[Computed]
    public function stockHealthScore()
    {
        $total = WarehouseInventory::count();
        if ($total === 0) return ['score' => 100, 'grade' => 'A'];

        $outOfStock = WarehouseInventory::where('status', 'Out of Stock')->count();
        $lowStock = WarehouseInventory::whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)->count();
        $healthy = $total - $outOfStock - $lowStock;

        $score = max(0, min(100, round(($healthy / $total) * 100)));
        
        return [
            'score' => $score,
            'healthy' => $healthy,
            'low' => $lowStock,
            'out' => $outOfStock,
            'total' => $total,
            'grade' => $score >= 90 ? 'A+' : ($score >= 75 ? 'A' : ($score >= 60 ? 'B' : ($score >= 40 ? 'C' : 'D'))),
        ];
    }

    #[Computed]
    public function totalStock()
    {
        return WarehouseInventory::sum('current_stock');
    }

    // ─── OPERATIONAL METRICS (API BASED) ────────

    #[Computed]
    public function warehouseStats()
    {
        try {
            $apiService = new WarehouseApiService();
            $data = $apiService->fetchSummary($this->startDate, $this->endDate);
            $metrics = $data['summary'] ?? [];

            return [
                'total_sepatu_dirak' => $metrics['stored_items'] ?? 0,
                'total_sepatu_finish_periode' => $metrics['finished_day'] ?? 0,
                'total_sepatu_diterima_periode' => $metrics['incoming_day'] ?? 0,
                'total_spk_print' => $metrics['spk_print'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_sepatu_dirak' => 0,
                'total_sepatu_finish_periode' => 0,
                'total_sepatu_diterima_periode' => 0,
                'total_spk_print' => 0,
            ];
        }
    }

    // ─── TABLE DATA ────────────────────────────

    #[Computed]
    public function allInventory()
    {
        return WarehouseInventory::query()
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sub_category', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->subCategoryFilter !== 'all', function($q) {
                $q->where('sub_category', $this->subCategoryFilter);
            })
            ->when($this->statusFilter !== 'all', function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('name', 'asc')
            ->paginate(15);
    }

    #[Computed]
    public function subCategoryBreakdown()
    {
        return WarehouseInventory::select(
                DB::raw("CASE WHEN sub_category IS NULL OR sub_category = '' THEN 'Material & Upper' ELSE sub_category END as sub_category_label"),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('SUM(current_stock) as total_stock'),
                DB::raw('SUM(total_valuation) as total_val'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->groupBy('sub_category_label')
            ->orderBy('total_val', 'desc')
            ->get();
    }

    #[Computed]
    public function subCategories()
    {
        return WarehouseInventory::select('sub_category')
            ->distinct()
            ->whereNotNull('sub_category')
            ->where('sub_category', '!=', '')
            ->orderBy('sub_category')
            ->pluck('sub_category');
    }

    #[Computed]
    public function topValueItems()
    {
        return WarehouseInventory::orderBy('total_valuation', 'desc')
            ->limit(10)
            ->get();
    }

    public function formatCurrency($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.warehouse-dashboard');
    }
}
