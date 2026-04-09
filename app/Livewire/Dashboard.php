<?php

namespace App\Livewire;

use App\Services\CalculationService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $selectedMonth = '';
    public bool $isSyncing = false;
    public ?string $lastSyncTime = null;

    public function mount(): void
    {
        if (empty($this->selectedMonth)) {
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }
        $this->updateSyncTime();
    }

    public function syncAll(): void
    {
        $syncService = app(\App\Services\SyncService::class);
        $marketingSyncService = app(\App\Services\MarketingSyncService::class);
        
        $secondsLeft = $syncService->getSecondsToNextSync('marketing_sync', 60);

        if ($secondsLeft > 0) {
            $this->dispatch('swal', [
                'title' => 'Tunggu Sebentar',
                'text' => "Data baru saja disinkronkan. Mohon tunggu {$secondsLeft} detik lagi.",
                'icon' => 'warning',
                'timer' => 3000,
                'toast' => true,
                'position' => 'top-end'
            ]);
            return;
        }

        // 1. Notify Start with a persistent modal
        $this->dispatch('swal', [
            'title' => 'Menyinkronkan Data',
            'text' => 'Sedang menarik data terupdate dari Meta, Shoeworkshop, dan Sleekflow. Mohon tunggu...',
            'icon' => 'info',
            'allowOutsideClick' => false,
            'showConfirmButton' => false,
            'willOpen' => true, // Flag for frontend to handle showLoading()
        ]);

        $this->isSyncing = true;

        // 2. Perform Sync
        $synced = $syncService->syncIfAllowed('marketing_sync', function() use ($marketingSyncService) {
            $marketingSyncService->syncMonth($this->selectedMonth);
        }, 60);

        if ($synced) {
            $this->dispatch('swal', [
                'title' => 'Sinkronisasi Berhasil',
                'text' => 'Seluruh performa iklan, omset, dan chat telah diperbarui.',
                'icon' => 'success',
                'timer' => 3000,
                'showConfirmButton' => true,
                'confirmButtonColor' => '#10b981',
            ]);
        }

        $this->updateSyncTime();
        $this->isSyncing = false;
    }

    /**
     * Update daily budget directly from the table
     */
    public function updateBudget($id, $value): void
    {
        // Cleanup value (remove non-numeric)
        $cleanValue = preg_replace('/[^0-9]/', '', $value);
        
        $report = \App\Models\DailyReport::find($id);
        if ($report) {
            $report->update([
                'budgeting' => (float) $cleanValue
            ]);

            $this->dispatch('swal', [
                'title' => 'Anggaran Diperbarui',
                'text' => 'Data anggaran tanggal ' . $report->date->format('d/m/Y') . ' berhasil disimpan.',
                'icon' => 'success',
                'timer' => 2000,
                'toast' => true,
                'position' => 'top-end',
                'showConfirmButton' => false
            ]);
        }
    }

    public function updateSyncTime(): void
    {
        $timestamp = app(\App\Services\SyncService::class)->getLastSyncTime('marketing_sync');
        $this->lastSyncTime = $timestamp ? Carbon::createFromTimestamp($timestamp)->diffForHumans() : null;
    }

    #[Computed]
    public function kpis(): array
    {
        $dashboardService = app(DashboardService::class);
        return $dashboardService->getKpis($this->selectedMonth . '-01');
    }

    #[Computed]
    public function dailyReports()
    {
        $dashboardService = app(DashboardService::class);
        return $dashboardService->getDailyReports($this->selectedMonth . '-01');
    }

    public function formatCurrency($amount): string
    {
        return app(CalculationService::class)->formatCurrency((float) $amount);
    }

    public function formatPercentage($amount): string
    {
        return app(CalculationService::class)->formatPercentage((float) $amount);
    }

    public function getRoasIndicator($current, $target): string
    {
        return app(CalculationService::class)->getRoasIndicator((float) $current, (float) $target);
    }

    public function getFormula(string $metric): string
    {
        return app(CalculationService::class)->getFormula($metric);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
