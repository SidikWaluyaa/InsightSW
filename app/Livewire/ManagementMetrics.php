<?php

namespace App\Livewire;

use App\Services\ManagementMetricsService;
use Carbon\Carbon;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Data Iklan & CS (config/management_metrics.php) di halaman Data Manajemen SW, dengan lampu MoM
 * (dibanding periode yang sama bulan lalu). Mengikuti rentang tanggal halaman induknya.
 */
#[Lazy]
class ManagementMetrics extends Component
{
    /** Rentang dari filter halaman induk (Y-m-d). */
    #[Reactive]
    public string $start;

    #[Reactive]
    public string $end;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-8 text-center text-sm text-slate-400 dark:text-gray-500 animate-pulse">
            Memuat Data Manajemen SW (Meta, Sleekflow, sistem pusat)…
        </div>
        HTML;
    }

    /**
     * Rentang terpilih, dipotong sampai kemarin (H-1): Sleekflow belum menyediakan data hari berjalan,
     * jadi semua sumber dipotong di tanggal yang sama supaya rasio antar sumber tetap adil.
     */
    protected function range(): ?array
    {
        $start = Carbon::parse($this->start)->startOfDay();
        $end = Carbon::parse($this->end)->startOfDay()->min(now()->subDay()->startOfDay());

        return $start->lte($end) ? [$start->toDateString(), $end->toDateString()] : null;
    }

    public function refreshData(ManagementMetricsService $service): void
    {
        if ($range = $this->range()) {
            $service->forget(...$range);
        }
    }

    public function showFormula(int $no, ManagementMetricsService $service): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! ($range = $this->range()) || ! config("management_metrics.data.{$no}")) {
            return;
        }

        $this->dispatch('show-formula', ...$service->explain($no, ...$range));
    }

    public function render(ManagementMetricsService $service)
    {
        $range = $this->range();
        $data = $range ? $service->collect(...$range) : null;
        $prevRange = $range ? $service->previousRange(...$range) : null;
        $prev = $prevRange ? $service->collect(...$prevRange) : null;
        $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];

        $groups = [];
        foreach (config('management_metrics.data') as $no => $def) {
            $value = $data['values'][$no] ?? null;
            $prevValue = $prev['values'][$no] ?? null;
            $mom = $service->compare($no, $value, $prevValue, $data['days'] ?? 1, $prev['days'] ?? 1);

            if ($mom['status']) {
                // Lampu utama: dibanding bulan lalu
                $badge = ($mom['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($mom['perubahan']), 1, ',', '.') . '% vs bulan lalu';
                $tone = $tones[$mom['status']];
            } else {
                // Cadangan: target Kamus (data tanpa arah MoM atau tanpa nilai bulan lalu)
                $met = $service->meetsTarget($no, $value, $data['days'] ?? 1);
                $badge = $met === null ? null : ($met ? 'Sesuai target Kamus' : 'Di luar target Kamus');
                $tone = $met ? 'emerald' : 'slate';
            }

            $groups[$def['kelompok']][$def['tahap']][$no] = $def + [
                'nilai' => $service->format($no, $value),
                'badge' => $badge,
                'badgeTone' => $tone,
                'bulanLalu' => $prevValue === null ? null : $service->format($no, $prevValue),
            ];
        }

        return view('livewire.management-metrics', [
            'range' => $range,
            'prevRange' => $prevRange,
            'trimmed' => $range && $range[1] !== $this->end,
            'groups' => $groups,
            'sourceErrors' => ($data['errors'] ?? []) + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors'] ?? []),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
        ]);
    }
}
