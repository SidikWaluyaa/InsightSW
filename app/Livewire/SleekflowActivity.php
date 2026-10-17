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

    /** Status tarik kontak bertahap (lihat tarikBagian). */
    public bool $menarik = false;

    public int $tarikOffset = 0;

    public int $tarikJumlah = 0;

    /** Halaman API kontak per request: 8 × (±1 dtk + jeda 0,5 dtk) jauh di bawah batas 30 dtk PHP. */
    protected const HALAMAN_PER_BAGIAN = 8;

    /**
     * Tarik kontak Sleekflow untuk rentang ini. API kontak dibaca halaman per halaman (lambat), jadi ditarik
     * bertahap: tiap request satu bagian, lalu browser memanggil bagian berikutnya sampai selesai.
     */
    public function tarikKontak(): void
    {
        if ($this->menarik) {
            return;
        }

        $this->menarik = true;
        $this->tarikOffset = 0;
        $this->tarikJumlah = 0;
        $this->tarikBagian(app(SleekflowService::class), app(ManagementMetricsService::class));
    }

    public function tarikBagian(SleekflowService $service, ManagementMetricsService $metrics): void
    {
        if (! $this->menarik) {
            return;
        }

        try {
            $result = $service->syncContacts($this->startDate, $this->endDate, $this->tarikOffset, self::HALAMAN_PER_BAGIAN);
        } catch (\Throwable $e) {
            Log::error('SleekflowActivity: tarik kontak gagal: ' . $e->getMessage());
            $this->menarik = false;
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Tarik kontak Sleekflow gagal: ' . $e->getMessage()]);

            return;
        }

        $this->tarikJumlah += $result['synced'] ?? 0;

        if (($result['next_offset'] ?? null) !== null) {
            $this->tarikOffset = $result['next_offset'];
            $this->dispatch('tarik-kontak-lanjut');

            return;
        }

        $this->menarik = false;
        $metrics->forget($this->startDate, $this->endDate);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Kontak Diperbarui',
            'text' => 'Sinkron ' . number_format($this->tarikJumlah, 0, ',', '.') . ' kontak Sleekflow untuk rentang ini.',
            'timer' => 3000,
        ]);
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
