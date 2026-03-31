<?php

namespace App\Livewire;

use App\Models\SleekflowContact;
use App\Services\SleekflowService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SleekflowManager extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate;
    public $endDate;
    public $statusFilter = '';
    public $isSyncing = false;

    protected $queryString = ['search', 'startDate', 'endDate', 'statusFilter'];

    public function mount()
    {
        $this->startDate = $this->startDate ?: Carbon::today()->format('Y-m-d');
        $this->endDate = $this->endDate ?: Carbon::today()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    #[On('set-status-filter')]
    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
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
    public function sync(SleekflowService $service)
    {
        \Illuminate\Support\Facades\Log::info('Sleekflow Sync Triggered');
        $this->isSyncing = true;
        
        try {
            $result = $service->syncContacts($this->startDate, $this->endDate);
            
            \Illuminate\Support\Facades\Log::info('Sync result: ' . json_encode($result));

            if (($result['synced'] ?? 0) === 0) {
                $this->dispatch('swal', [
                    'icon'    => 'info',
                    'title'   => 'Sudah Mutakhir',
                    'text'    => $result['message'] ?? 'Tidak ada perubahan data baru dari Sleekflow.',
                    'timer'   => 3000
                ]);
            } else {
                $this->dispatch('swal', [
                    'icon'    => 'success',
                    'title'   => 'Pembaruan Selesai',
                    'text'    => "Berhasil sinkronisasi {$result['synced']} data terbaru dari Sleekflow.",
                    'timer'   => 5000
                ]);
            }
            
            $this->resetPage();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Sleekflow Sync Error: ' . $e->getMessage());
            $this->dispatch('swal', [
                'icon'    => 'error',
                'title'   => 'Gagal Sinkronisasi',
                'text'    => 'Terjadi kesalahan saat menghubungi API Sleekflow: ' . $e->getMessage(),
            ]);
        }
        
        $this->isSyncing = false;
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
            ->orderBy('created_at_sleekflow', 'desc');

        return view('livewire.sleekflow-manager', array_merge($analytics, [
            'contacts' => $query->paginate(20),
            'uniqueStatuses' => $uniqueStatuses,
        ]));
    }
}
