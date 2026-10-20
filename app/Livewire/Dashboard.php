<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
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
    use WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    public bool $isSyncing = false;
    public ?string $lastSyncTime = null;
    public int $lastGlobalSyncTrigger = 0;

    // Sequential Sync State
    public array $syncSteps = [];
    public int $currentStepIndex = 0;
    public string $syncMessage = '';

    public function mount(): void
    {
        $today = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $today->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $today->toDateString()
        );
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

        // 1. Bagi rentang terpilih jadi blok 3 hari
        $start = Carbon::parse($this->startDate);
        $endOfMonth = Carbon::parse($this->endDate)->min(Carbon::now());
        
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
            // Only sync payments on the first chunk to save API resources
            $marketingSyncService->syncRange($chunk['start'], $chunk['end'], skipPaymentSync: $this->currentStepIndex > 0);
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
            'text' => 'Seluruh performa iklan, omset, dan chat untuk rentang ini telah diperbarui.',
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
    public function updateBudget(int|string $id, mixed $value): void
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

    /**
     * Laporan harian pada rentang terpilih. DashboardService masih bekerja per bulan (omset per hari
     * dipetakan lewat nomor tanggal), jadi diambil per bulan lalu dipotong ke rentang.
     */
    #[Computed]
    public function dailyReports()
    {
        $dashboardService = app(DashboardService::class);
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $reports = collect();
        for ($month = $start->copy()->startOfMonth(); $month->lte($end); $month->addMonthNoOverflow()) {
            $reports = $reports->concat($dashboardService->getDailyReports($month->toDateString()));
        }

        return $reports
            ->filter(fn ($report) => $report->date->between($start, $end->copy()->endOfDay()))
            ->sortByDesc('date')
            ->values();
    }

    public function formatCurrency(int|float $amount): string
    {
        return app(CalculationService::class)->formatCurrency((float) $amount);
    }

    public function formatPercentage(int|float $amount): string
    {
        return app(CalculationService::class)->formatPercentage((float) $amount);
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
