<?php

namespace App\Livewire;

use App\Models\SleekflowContact;
use App\Services\SleekflowService;
use App\Services\SyncService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Livewire\Concerns\WithDateRange;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SleekflowManager extends Component
{
    use WithPagination;
    use WithDateRange;

    public $search = '';
    public $startDate;
    public $endDate;
    public $statusFilter = '';
    public $gapFilter = '';
    public $lastSyncTimestamp;
    public $isSyncing = false;

    public int $tarikOffset = 0;
    public int $tarikJumlah = 0;
    protected const HALAMAN_PER_BAGIAN = 5;

    protected $queryString = ['search', 'startDate', 'endDate', 'statusFilter', 'gapFilter'];

    public function mount()
    {
        $this->startDate = $this->startDate ?: Carbon::today()->format('Y-m-d');
        $this->endDate = $this->endDate ?: Carbon::today()->format('Y-m-d');
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
    }

    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(SyncService::class)->getLastSyncTime('sleekflow_manager_sync');
    }

    public function checkSync()
    {
        // Auto sync only for today
        if ($this->startDate !== now()->format('Y-m-d')) {
            return;
        }

        // Jangan jalankan background sync jika sedang proses tarik manual
        if ($this->isSyncing) {
            return;
        }

        app(SyncService::class)->syncIfAllowed('sleekflow_manager_sync', function() {
            // Tarik 2 halaman terbaru untuk background polling cepat tanpa membebani server
            app(SleekflowService::class)->syncContacts(now()->format('Y-m-d'), now()->format('Y-m-d'), 0, 2);
        }, 60);

        $this->updateSyncState();
    }

    /** Dipanggil setelah rentang diubah lewat <x-ui.range-toolbar>. */
    protected function dateRangeChanged(): void
    {
        $this->resetPage();
    }

    #[On('set-date-filters')]
    public function setDateFilters($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->resetPage();
    }

    #[On('sync-sleekflow')]
    public function sync(SleekflowService $service): void
    {
        if ($this->isSyncing) {
            return;
        }

        $this->isSyncing = true;
        $this->tarikOffset = 0;
        $this->tarikJumlah = 0;
        $this->tarikBagian($service);
    }

    public function tarikBagian(SleekflowService $service): void
    {
        if (!$this->isSyncing) {
            return;
        }

        try {
            $result = $service->syncContacts($this->startDate, $this->endDate, $this->tarikOffset, self::HALAMAN_PER_BAGIAN);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SleekflowManager: tarik kontak gagal: ' . $e->getMessage());
            $this->isSyncing = false;
            $this->dispatch('swal', [
                'icon'    => 'error',
                'title'   => 'Gagal Sinkronisasi',
                'text'    => 'Terjadi kesalahan saat menghubungi API Sleekflow: ' . $e->getMessage(),
            ]);
            return;
        }

        $this->tarikJumlah += $result['synced'] ?? 0;

        // Jika masih ada halaman berikutnya, request chunk berikutnya lewat event browser
        if (($result['next_offset'] ?? null) !== null) {
            $this->tarikOffset = $result['next_offset'];
            $this->dispatch('tarik-kontak-lanjut');
            return;
        }

        // Selesai seluruhnya
        $this->isSyncing = false;
        $this->updateSyncState();
        $this->resetPage();

        if ($this->tarikJumlah === 0) {
            $this->dispatch('swal', [
                'icon'    => 'info',
                'title'   => 'Sudah Mutakhir',
                'text'    => 'Tidak ada perubahan data baru dari Sleekflow.',
                'timer'   => 3000
            ]);
        } else {
            $this->dispatch('swal', [
                'icon'    => 'success',
                'title'   => 'Pembaruan Selesai',
                'text'    => "Berhasil sinkronisasi " . number_format($this->tarikJumlah, 0, ',', '.') . " data terbaru dari Sleekflow.",
                'timer'   => 5000
            ]);
        }
    }

    #[On('set-gap-filter')]
    public function setGapFilter($gap)
    {
        $this->gapFilter = $gap;
        $this->resetPage();
    }

    #[On('set-status-filter')]
    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function render(SleekflowService $service)
    {
        $uniqueStatuses = SleekflowContact::whereNotNull('status_chat')
            ->distinct()
            ->pluck('status_chat')
            ->filter()
            ->toArray();

        // Get analytics data from Service Layer
        $analytics = $service->getAnalyticsData($this->startDate, $this->endDate);

        // Query for table (honors all filters)
        $query = SleekflowContact::query()
            ->when($this->startDate && $this->endDate, function($q) {
                // If filtering by gap, we might want to see contacts from any time, 
                // but usually the user wants to see contacts CREATED in this range.
                // However, for SLA tracking, sometimes we want to see ALL pending chats regardless of creation date.
                // For now, I'll keep the date range as primary filter unless gap is set for 'all time' check.
                $q->whereBetween('created_at_sleekflow', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->when($this->search, function ($q) {
                $q->where(function($inner) {
                    $inner->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function($q) {
                $q->where('status_chat', $this->statusFilter);
            })
            ->when($this->gapFilter, function($q) {
                $days = (int)$this->gapFilter;
                if ($days > 0) {
                    $threshold = now()->subDays($days);
                    $q->where(function($sub) use ($threshold) {
                        // Case 1: Company never responded AND customer message is old enough
                        $sub->where(function($inner) use ($threshold) {
                            $inner->whereNull('last_contacted_from_company')
                                  ->whereNotNull('last_contact_from_customers')
                                  ->where('last_contact_from_customers', '<=', $threshold);
                        })
                        // Case 2: Customer message is newer than company response AND customer message is old enough
                        ->orWhere(function($inner) use ($threshold) {
                            $inner->whereNotNull('last_contacted_from_company')
                                  ->whereColumn('last_contact_from_customers', '>', 'last_contacted_from_company')
                                  ->where('last_contact_from_customers', '<=', $threshold);
                        });
                    });
                }
            })
            ->orderBy('created_at_sleekflow', 'desc');

        return view('livewire.sleekflow-manager', array_merge($analytics, [
            'contacts' => $query->paginate(20),
            'uniqueStatuses' => $uniqueStatuses,
        ]));
    }
}
