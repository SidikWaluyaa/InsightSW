<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseInventory;
use App\Models\WarehouseRequest;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseSyncService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSubCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        //
    }

    public function syncAll()
    {
        $this->isSyncing = true;
        
        $service = new WarehouseSyncService();
        $invResult = $service->syncInventory();
        $reqResult = $service->syncRequests();
        $trxResult = $service->syncTransactions();

        if ($invResult['success'] && $reqResult['success'] && $trxResult['success']) {
            $this->dispatch('swal', [
                'title' => 'Sync Berhasil',
                'text' => 'Seluruh data operasional Gudang telah terbarui.',
                'icon' => 'success',
                'timer' => 3000
            ]);
        } else {
            $this->dispatch('swal', [
                'title' => 'Sync Error',
                'text' => 'Beberapa data gagal ditarik. Cek log untuk detail.',
                'icon' => 'error',
                'timer' => 3000
            ]);
        }

        $this->isSyncing = false;
    }

    // ─── KPI METRICS ───────────────────────────

    #[Computed]
    public function assetValuation()
    {
        $valuation = WarehouseInventory::select('category', DB::raw('SUM(total_valuation) as total'))
            ->groupBy('category')
            ->get();

        $grandTotal = $valuation->sum('total');

        return [
            'grand_total' => $grandTotal,
            'categories' => $valuation
        ];
    }

    #[Computed]
    public function totalItems()
    {
        return WarehouseInventory::count();
    }

    #[Computed]
    public function totalStock()
    {
        return WarehouseInventory::sum('current_stock');
    }

    #[Computed]
    public function outOfStockCount()
    {
        return WarehouseInventory::where('status', 'Out of Stock')->count();
    }

    #[Computed]
    public function lowStockCount()
    {
        return WarehouseInventory::whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)
            ->count();
    }

    // ─── ALGORITHMIC INSIGHTS ──────────────────

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
    public function stockHealthScore()
    {
        // Score 0-100 based on stock health metrics
        $total = WarehouseInventory::count();
        if ($total === 0) return 100;

        $outOfStock = WarehouseInventory::where('status', 'Out of Stock')->count();
        $lowStock = WarehouseInventory::whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)->count();
        $healthy = $total - $outOfStock - $lowStock;

        // Weighted: out of stock = -3 pts, low stock = -1 pt per item
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
    public function topValueItems()
    {
        return WarehouseInventory::orderBy('total_valuation', 'desc')
            ->limit(10)
            ->get();
    }

    // ─── CRISIS ALERTS ─────────────────────────

    #[Computed]
    public function lowStockAlerts()
    {
        return WarehouseInventory::where(function($q) {
                $q->whereColumn('current_stock', '<=', 'min_stock')
                  ->orWhere('status', 'Out of Stock');
            })
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sub_category', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('current_stock', 'asc')
            ->limit(30)
            ->get();
    }

    #[Computed]
    public function fulfillmentHealth()
    {
        $threeDaysAgo = Carbon::now()->subDays(3);
        
        $bottlenecks = WarehouseRequest::where('status', 'PENDING')
            ->where('requested_at', '<', $threeDaysAgo)
            ->count();

        $allPending = WarehouseRequest::where('status', 'PENDING')->count();

        return [
            'bottleneck_count' => $bottlenecks,
            'total_pending' => $allPending
        ];
    }

    #[Computed]
    public function auditIntegrity()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return WarehouseTransaction::where('type', 'ADJUSTMENT')
            ->where('transaction_date', '>=', $thirtyDaysAgo)
            ->count();
    }

    // ─── INVENTORY TABLE (Paginated) ───────────

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
    public function subCategories()
    {
        return WarehouseInventory::select('sub_category')
            ->distinct()
            ->whereNotNull('sub_category')
            ->where('sub_category', '!=', '')
            ->orderBy('sub_category')
            ->pluck('sub_category');
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.warehouse-dashboard');
    }
}
