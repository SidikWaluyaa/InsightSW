<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use App\Services\ManagementMetricsService;
use App\Services\SleekflowActivityService;
use App\Services\SleekflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * CS → SleekFlow Activity (PRD halaman 2): beban kerja & SLA. KPI dari analitik harian Sleekflow (lengkap),
 * beban per agen & lifecycle dari kontak Sleekflow (lengkap hanya untuk tanggal yang sudah ditarik).
 */
#[Layout('layouts.app')]
class SleekflowActivity extends Component
{
    use WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    public function mount(): void
    {
        $max = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $max->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $max->toDateString()
        );
    }

    /** Analitik harian Sleekflow baru lengkap besok, jadi paling akhir kemarin (H-1). */
    protected function maxSelectableDate(): Carbon
    {
        return now()->subDay()->startOfDay();
    }

    /** Tarik kontak Sleekflow untuk rentang ini (lambat: API kontak dibaca halaman per halaman). */
    public function tarikKontak(SleekflowService $service, ManagementMetricsService $metrics): void
    {
        try {
            $result = $service->syncContacts($this->startDate, $this->endDate);
            $metrics->forget($this->startDate, $this->endDate);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Kontak Diperbarui',
                'text' => 'Sinkron ' . ($result['synced'] ?? 0) . ' kontak Sleekflow untuk rentang ini.',
                'timer' => 3000,
            ]);
        } catch (\Throwable $e) {
            Log::error('SleekflowActivity: tarik kontak gagal: ' . $e->getMessage());
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Tarik kontak Sleekflow gagal: ' . $e->getMessage()]);
        }
    }

    public function showFormula(int $no, ManagementMetricsService $metrics): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (in_array($no, [15, 17], true)) {
            $this->dispatch('show-formula', ...$metrics->explain($no, $this->startDate, $this->endDate));
        }
    }

    public function render(SleekflowActivityService $activity)
    {
        $kpis = $activity->kpis($this->startDate, $this->endDate);

        return view('livewire.sleekflow-activity', [
            'kpis' => $kpis,
            'coverage' => $activity->coverage($this->startDate, $this->endDate, $kpis['chat']['nilai']),
            'agents' => $activity->agents($this->startDate, $this->endDate),
            'lifecycle' => $activity->lifecycle($this->startDate, $this->endDate),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
