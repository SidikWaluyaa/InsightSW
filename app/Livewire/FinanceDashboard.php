<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\FinanceSync;
use App\Services\FinanceSyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class FinanceDashboard extends Component
{
    use \Livewire\WithPagination;

    public $isSyncing = false;
    public $lastSync;
    public $startDate;
    public $endDate;
    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->lastSync = FinanceSync::latest('updated_at')->first()?->updated_at;
    }

    public function syncPusat(FinanceSyncService $service)
    {
        $this->isSyncing = true;
        
        // Always sync the last 60 days to catch status updates for older records
        $result = $service->syncRolling(60, \Illuminate\Support\Facades\Auth::id());
        
        if ($result['success']) {
            $this->dispatch('swal', [
                'title' => 'Sinkronisasi Berhasil',
                'text' => $result['message'] . ' (60 Hari Terakhir)',
                'icon' => 'success',
                'timer' => 4000
            ]);
        } else {
            $this->dispatch('swal', [
                'title' => 'Gagal Sinkronisasi',
                'text' => $result['message'],
                'icon' => 'error',
                'timer' => 5000
            ]);
        }

        $this->lastSync = FinanceSync::latest('updated_at')->first()?->updated_at;
        $this->isSyncing = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $start = $this->startDate . ' 00:00:00';
        $end = $this->endDate . ' 23:59:59';

        $query = FinanceSync::query()
            ->whereBetween('source_created_at', [$start, $end]);

        if ($this->statusFilter) {
            // Support BB (Database BB), BL (Database BL), and L (Database L)
            // Keep PL as catch-all for BB/BL to handle the current session transition
            if ($this->statusFilter === 'BB' || $this->statusFilter === 'B') {
                $query->where('status_pembayaran', 'BB');
            } elseif ($this->statusFilter === 'BL' || $this->statusFilter === 'C') {
                $query->where('status_pembayaran', 'BL');
            } elseif ($this->statusFilter === 'PL') {
                $query->whereIn('status_pembayaran', ['BB', 'BL']);
            } elseif ($this->statusFilter === 'L') {
                $query->where('status_pembayaran', 'L');
            }
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('spk_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }

        $transactions = $query->orderBy('source_created_at', 'DESC')->paginate(10);

        return view('livewire.finance-dashboard', [
            'transactions' => $transactions,
            'stats' => $this->stats,
        ]);
    }

    #[Computed]
    public function stats()
    {
        $start = $this->startDate . ' 00:00:00';
        $end = $this->endDate . ' 23:59:59';

        $baseQuery = FinanceSync::whereBetween('source_created_at', [$start, $end]);

        $totalBill = $baseQuery->sum('total_bill');
        $totalPaid = $baseQuery->sum('amount_paid');
        $totalRemaining = $baseQuery->sum('remaining_balance');

        $collectionRate = $totalBill > 0 ? ($totalPaid / $totalBill) * 100 : 0;

        return [
            'total_bill' => $totalBill,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'collection_rate' => $collectionRate,
            'lunas_count' => (clone $baseQuery)->where('status_pembayaran', 'L')->count(),
            'total_count' => (clone $baseQuery)->count(),
        ];
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
