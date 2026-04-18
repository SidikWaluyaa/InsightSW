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
    public int $lastGlobalSyncTrigger = 0;

    // Sequential Sync State
    public array $syncSteps = [];
    public int $currentStepIndex = 0;
    public string $syncMessage = '';

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
        $secondsLeft = $syncService->getSecondsToNextSync('marketing_sync', 30); // Reduced throttle for chunks

        if ($secondsLeft > 0 && !$this->isSyncing) {
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

        $this->isSyncing = true;
        $this->currentStepIndex = 0;
        $this->syncSteps = [];

        // 1. Calculate Chunks (3-day blocks)
        $monthDate = Carbon::parse($this->selectedMonth . '-01');
        $start = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->isCurrentMonth() ? Carbon::now() : $monthDate->copy()->endOfMonth();
        
        $current = $start->copy();
        while ($current->lte($endOfMonth)) {
            $chunkEnd = $current->copy()->addDays(2);
            if ($chunkEnd->gt($endOfMonth)) $chunkEnd = $endOfMonth->copy();
            
            $this->syncSteps[] = [
                'start' => $current->toDateString(),
                'end' => $chunkEnd->toDateString(),
                'label' => $current->format('d M') . ' - ' . $chunkEnd->format('d M')
            ];
            
            $current = $chunkEnd->copy()->addDay();
        }

        $this->syncMessage = "Menyiapkan sinkronisasi " . count($this->syncSteps) . " blok data...";
        
        // 2. Start the chain
        $this->dispatch('sync-started', total: count($this->syncSteps));
        $this->syncNextChunk();
    }

    public function syncNextChunk(): void
    {
        if ($this->currentStepIndex >= count($this->syncSteps)) {
            $this->finishSync();
            return;
        }

        $chunk = $this->syncSteps[$this->currentStepIndex];
        $this->syncMessage = "Memproses " . ($this->currentStepIndex + 1) . "/" . count($this->syncSteps) . ": " . $chunk['label'];

        $marketingSyncService = app(\App\Services\MarketingSyncService::class);
        $syncService = app(\App\Services\SyncService::class);

        // We use a shorter lock for chunks
        $synced = $syncService->syncIfAllowed('marketing_sync', function() use ($marketingSyncService, $chunk) {
            $marketingSyncService->syncRange($chunk['start'], $chunk['end']);
        }, 0); // Disable throttle for sequential chunks

        $this->currentStepIndex++;
        
        if ($this->currentStepIndex >= count($this->syncSteps)) {
            $this->finishSync();
        } else {
            // Signal browser to trigger next chunk to avoid PHP timeout
            $this->dispatch('chunk-completed', 
                nextIndex: $this->currentStepIndex, 
                progress: round(($this->currentStepIndex / count($this->syncSteps)) * 100)
            );
        }
    }

    protected function finishSync(): void
    {
        $this->isSyncing = false;
        $this->syncMessage = "Sinkronisasi Selesai!";
        $this->updateSyncTime();
        
        // Trigger cross-tab reload for other dashboards
        \Illuminate\Support\Facades\Cache::put('global_sync_trigger', now()->timestamp, now()->addMinutes(10));

        $this->dispatch('sync-finished');
        $this->dispatch('swal', [
            'title' => 'Sinkronisasi Berhasil',
            'text' => 'Seluruh performa iklan, omset, dan chat untuk bulan ini telah diperbarui.',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function checkSync()
    {
        // Detect if a global sync happened in another tab
        $globalSyncTrigger = (int) \Illuminate\Support\Facades\Cache::get('global_sync_trigger', 0);
        if ($globalSyncTrigger > $this->lastGlobalSyncTrigger) {
            $this->lastGlobalSyncTrigger = $globalSyncTrigger;
            $this->updateSyncTime();
            // Native re-render will pick up new computed properties
        }
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
