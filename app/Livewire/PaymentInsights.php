<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\PaymentSync;
use App\Services\PaymentSyncService;
use App\Services\SyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentInsightExport;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class PaymentInsights extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all'; // all, unpaid, paid
    public $dateFilterType = 'paid_at'; // paid_at, source_created_at
    public $startDate;
    public $endDate;
    public $analyticsStartDate;
    public $analyticsEndDate;
    public $isSyncing = false;
    public $lastSyncTime;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'dateFilterType' => ['except' => 'paid_at'],
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
        'analyticsStartDate' => ['except' => null],
        'analyticsEndDate' => ['except' => null],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilterType()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->dispatchData();
    }

    public function updatedEndDate()
    {
        $this->dispatchData();
    }

    public function updatedAnalyticsStartDate()
    {
        $this->dispatchData();
    }

    public function updatedAnalyticsEndDate()
    {
        $this->dispatchData();
    }

    public function dispatchData()
    {
        $this->dispatch('revenue-data-updated', [
            'revenueData' => $this->dailyRevenue
        ]);
    }

    public function mount()
    {
        $this->updateSyncState();
        $this->syncData(); // Sync on first load

        // Default Analytics to last 14 days
        $this->analyticsStartDate = now()->subDays(13)->format('Y-m-d');
        $this->analyticsEndDate = now()->format('Y-m-d');
    }

    public function updateSyncState()
    {
        $this->lastSyncTime = app(SyncService::class)->getLastSyncTime('payment_insights_sync');
    }

    public function syncData()
    {
        $this->isSyncing = true;
        
        $result = app(PaymentSyncService::class)->sync();
        
        if ($result['success']) {
            \Illuminate\Support\Facades\Cache::put("sync_last_time_payment_insights_sync", time(), now()->addDay());
            $this->updateSyncState();
            
            $count = $result['count'] ?? 0;
            $this->dispatch('swal', [
                'title' => 'Sinkron Selesai',
                'text' => $count > 0 ? "Berhasil mendapatkan $count catatan pembayaran baru." : "Data pembayaran sudah yang terbaru. Tidak ada data baru.",
                'icon' => 'success',
                'timer' => 3000
            ]);
            $this->dispatchData(); // Update chart after sync
        } else {
            $this->dispatch('swal', [
                'title' => 'Sync Gagal',
                'text' => $result['message'] ?? 'Terjadi kesalahan saat menghubungi API.',
                'icon' => 'error',
                'timer' => 3000
            ]);
        }
        
        $this->isSyncing = false;
    }

    #[Computed]
    public function dailyRevenue()
    {
        // Use separate analytics filters
        $start = $this->analyticsStartDate ? Carbon::parse($this->analyticsStartDate)->startOfDay() : now()->subDays(13)->startOfDay();
        $end = $this->analyticsEndDate ? Carbon::parse($this->analyticsEndDate)->endOfDay() : now()->endOfDay();

        return PaymentSync::query()
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount_paid) as total')
            )
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    #[Computed]
    public function maxDailyRevenue()
    {
        return $this->dailyRevenue->max('total') ?: 1;
    }


    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getFilteredQuery()
    {
        $query = PaymentSync::query();

        if ($this->statusFilter !== 'all') {
            // Filter to show only the LATEST payment for each invoice
            $query->whereIn('id', function($subQuery) {
                $subQuery->select(DB::raw('MAX(id)'))
                         ->from('payment_syncs')
                         ->groupBy('spk_number');
            });

            if ($this->statusFilter === 'unpaid') {
                $query->where('balance_snapshot', '>', 0);
            } elseif ($this->statusFilter === 'paid') {
                $query->where('balance_snapshot', '<=', 0);
            }
        }

        return $query->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('spk_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->startDate, function($query) {
                $query->whereDate($this->dateFilterType, '>=', $this->startDate);
            })
            ->when($this->endDate, function($query) {
                $query->whereDate($this->dateFilterType, '<=', $this->endDate);
            })
            ->orderBy($this->dateFilterType, 'desc');
    }

    public function exportExcel()
    {
        $query = $this->getFilteredQuery();
        return Excel::download(new PaymentInsightExport($query), 'Payment_Insights_' . now()->format('YmdHis') . '.xlsx');
    }

    public function exportPdf()
    {
        $payments = $this->getFilteredQuery()->get();
        
        $pdf = Pdf::loadView('exports.payment-pdf', [
            'payments' => $payments,
            'statusFilter' => $this->statusFilter,
            'search' => $this->search,
        ]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Payment_Insights_' . now()->format('YmdHis') . '.pdf');
    }

    public function render()
    {
        $payments = $this->getFilteredQuery()->paginate(15);

        return view('livewire.payment-insights', [
            'payments' => $payments
        ]);
    }
}
