<?php

namespace App\Livewire;

use Livewire\Component;

use App\Services\WorkshopSyncService;
use App\Models\WorkshopMatrix;
use App\Models\WorkshopMetric;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WorkshopDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $activeFilter = 'bulan_ini';
    public $isSyncing = false;

    public function mount()
    {
        $this->applyFilter('bulan_ini', false);
    }

    /**
     * Apply a preset or custom date filter.
     * 
     * @param string $filter Filter key: hari_ini, 7_hari, bulan_ini, 3_bulan, tahun_ini, minggu_ini, kemarin, 30_hari
     * @param bool $doSync Whether to trigger sync after applying the filter
     */
    public function applyFilter($filter, $doSync = true)
    {
        $this->activeFilter = $filter;

        switch ($filter) {
            case 'hari_ini':
                $this->startDate = now()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'kemarin':
                $this->startDate = now()->subDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->format('Y-m-d');
                break;
            case 'minggu_ini':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '7_hari':
                $this->startDate = now()->subDays(6)->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'bulan_ini':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '30_hari':
                $this->startDate = now()->subDays(29)->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '3_bulan':
                $this->startDate = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'tahun_ini':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
        }

        if ($doSync) {
            $this->sync();
        }
    }

    /**
     * When user manually changes start/end date, switch to 'kustom' mode.
     */
    public function updatedStartDate()
    {
        $this->activeFilter = 'kustom';
        $this->sync();
    }

    public function updatedEndDate()
    {
        $this->activeFilter = 'kustom';
        $this->sync();
    }

    public function sync($silent = false)
    {
        $this->isSyncing = true;
        
        try {
            $service = app(WorkshopSyncService::class);
            $result = $service->sync($this->startDate, $this->endDate);

            if ($result['success']) {
                $freshMetrics = WorkshopMetric::latest('last_sync_at')->first();
                $this->dispatch('revenue-data-updated', metrics: $freshMetrics);
                
                if (!$silent) {
                    $this->dispatch('swal', [
                        'title' => 'Sinkronisasi Berhasil',
                        'text' => 'Data operasional Workshop telah diperbarui.',
                        'icon' => 'success'
                    ]);
                }
            } else {
                if (!$silent) {
                    $this->dispatch('swal', [
                        'title' => 'Sinkronisasi Gagal',
                        'text' => $result['message'],
                        'icon' => 'error'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Workshop Dashboard Sync Error: " . $e->getMessage());
        }

        $this->isSyncing = false;
    }

    #[Computed]
    public function matrix()
    {
        return WorkshopMatrix::all()->groupBy('phase');
    }

    #[Computed]
    public function metrics()
    {
        return WorkshopMetric::latest('last_sync_at')->first() ?? new WorkshopMetric();
    }

    /**
     * Get a human-readable label for the active filter period.
     */
    #[Computed]
    public function filterLabel()
    {
        $labels = [
            'hari_ini' => 'Hari Ini',
            'kemarin' => 'Kemarin',
            'minggu_ini' => 'Minggu Ini',
            '7_hari' => '7 Hari Terakhir',
            'bulan_ini' => 'Bulan Ini',
            '30_hari' => '30 Hari Terakhir',
            '3_bulan' => '3 Bulan Terakhir',
            'tahun_ini' => 'Tahun Ini',
            'kustom' => 'Kustom',
        ];

        return $labels[$this->activeFilter] ?? 'Kustom';
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.workshop-dashboard');
    }
}
