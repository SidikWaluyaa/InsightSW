<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use App\Models\CsDropoffSurvey;
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

    /** Form input survei alasan drop-off (periode sampel default = rentang yang sedang dilihat). */
    public string $dropMulai = '';

    public string $dropSelesai = '';

    public string $dropAlasan = '';

    public string $dropJumlah = '';

    public string $dropCatatan = '';

    /** Peran yang boleh menginput / menghapus hasil survei. */
    protected const INPUT_ROLES = ['Admin', 'Editor', 'Leader CS'];

    public function mount(): void
    {
        $max = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $max->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $max->toDateString()
        );
        [$this->dropMulai, $this->dropSelesai] = [$this->startDate, $this->endDate];
    }

    protected function dateRangeChanged(): void
    {
        [$this->dropMulai, $this->dropSelesai] = [$this->startDate, $this->endDate];
    }

    protected function canInputDropoff(): bool
    {
        return auth()->user()?->hasRole(self::INPUT_ROLES) ?? false;
    }

    /** Simpan satu baris hasil survei: periode sampel, alasan, jumlah chat. */
    public function simpanAlasan(): void
    {
        abort_unless($this->canInputDropoff(), 403);

        $data = $this->validate([
            'dropMulai' => ['required', 'date_format:Y-m-d'],
            'dropSelesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:dropMulai'],
            'dropAlasan' => ['required', \Illuminate\Validation\Rule::in(CsDropoffSurvey::ALASAN)],
            'dropJumlah' => ['required', 'integer', 'min:1', 'max:100000'],
            'dropCatatan' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => ':Attribute wajib diisi.',
            'integer' => ':Attribute harus berupa angka.',
            'min' => ':Attribute minimal :min.',
            'max' => ':Attribute terlalu besar.',
            'date_format' => ':Attribute tidak valid.',
            'after_or_equal' => 'Akhir periode tidak boleh sebelum awal periode.',
            'in' => ':Attribute tidak dikenal.',
        ], [
            'dropMulai' => 'awal periode', 'dropSelesai' => 'akhir periode', 'dropAlasan' => 'alasan',
            'dropJumlah' => 'jumlah chat', 'dropCatatan' => 'catatan',
        ]);

        CsDropoffSurvey::create([
            'periode_mulai' => $data['dropMulai'],
            'periode_selesai' => $data['dropSelesai'],
            'alasan' => $data['dropAlasan'],
            'jumlah' => (int) $data['dropJumlah'],
            'catatan' => $data['dropCatatan'] ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->reset('dropAlasan', 'dropJumlah', 'dropCatatan');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tersimpan', 'text' => 'Hasil survei drop-off ditambahkan.', 'timer' => 2000, 'toast' => true, 'position' => 'top-end']);
    }

    public function hapusAlasan(int $id): void
    {
        abort_unless($this->canInputDropoff(), 403);

        CsDropoffSurvey::whereKey($id)->delete();
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

    /**
     * Rekap survei drop-off yang periodenya beririsan dengan rentang terpilih: jumlah & persen per alasan,
     * plus daftar baris input (untuk dihapus bila salah).
     */
    protected function dropoff(): array
    {
        $rows = CsDropoffSurvey::overlapping($this->startDate, $this->endDate)->with('creator:id,name')->orderByDesc('periode_mulai')->get();
        $total = $rows->sum('jumlah');

        $rekap = $rows->groupBy('alasan')
            ->map(fn ($g, $alasan) => ['alasan' => $alasan, 'jumlah' => $g->sum('jumlah'), 'persen' => $total ? $g->sum('jumlah') / $total * 100 : 0])
            ->sortByDesc('jumlah')->values()->all();

        return ['rekap' => $rekap, 'total' => $total, 'baris' => $rows];
    }

    public function render(SleekflowActivityService $activity)
    {
        $kpis = $activity->kpis($this->startDate, $this->endDate);

        return view('livewire.sleekflow-activity', [
            'kpis' => $kpis,
            'coverage' => $activity->coverage($this->startDate, $this->endDate, $kpis['chat']['nilai']),
            'agents' => $activity->agents($this->startDate, $this->endDate),
            'lifecycle' => $activity->lifecycle($this->startDate, $this->endDate),
            'dropoff' => $this->dropoff(),
            'canInputDropoff' => $this->canInputDropoff(),
            'alasanList' => CsDropoffSurvey::ALASAN,
            'showFormula' => auth()->user()?->isAdmin() ?? false,
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
