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

    public $lastSyncTimestamp;
    public $isSyncing = false;
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

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
    }

    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(\App\Services\SyncService::class)->getLastSyncTime('finance_sync');
    }

    public function checkSync()
    {
        // Polling logic
        $this->isSyncing = true;
        
        app(\App\Services\SyncService::class)->syncIfAllowed('finance_sync', function() {
            app(FinanceSyncService::class)->syncRolling(60, \Illuminate\Support\Facades\Auth::id());
        }, 60);

        $this->updateSyncState();
        $this->isSyncing = false;
    }

    public function syncPusat(FinanceSyncService $service)
    {
        $this->isSyncing = true;
        
        // Always sync the last 60 days to catch status updates for older records
        $result = $service->syncRolling(60, \Illuminate\Support\Facades\Auth::id());
        
        if ($result['success']) {
            // Update timestamp manually since we forced it
            \Illuminate\Support\Facades\Cache::put("sync_last_time_finance_sync", time(), now()->addDays(1));
            $this->updateSyncState();

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
        $totalShipping = $baseQuery->sum('shipping_cost');
        $totalNetRevenue = $totalBill - $totalShipping;
        
        $totalPaid = $baseQuery->sum('amount_paid');
        $totalRemaining = $baseQuery->sum('remaining_balance');

        $collectionRate = $totalBill > 0 ? ($totalPaid / $totalBill) * 100 : 0;

        return [
            'total_bill' => $totalBill,
            'total_net_revenue' => $totalNetRevenue,
            'total_shipping' => $totalShipping,
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
