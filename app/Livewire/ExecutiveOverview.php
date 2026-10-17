<?php

namespace App\Livewire;

use App\Services\ManagementMetricsService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Executive Overview di Dashboard (PRD Master Blueprint v3.0, halaman 1): 4 kartu utama, corong chat sampai
 * sepatu tiba, tren omset vs biaya iklan, dan SPK per jenis. Angka memakai sumber yang sama dengan
 * Data Manajemen SW, lampu dibanding periode yang sama bulan lalu (MoM).
 */
#[Lazy]
class ExecutiveOverview extends Component
{
    #[Reactive]
    public string $start;

    #[Reactive]
    public string $end;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-8 text-center text-sm text-slate-400 dark:text-gray-500 animate-pulse">
            Memuat ringkasan eksekutif (Meta, Sleekflow, sistem pusat)…
        </div>
        HTML;
    }

    /** Sama dengan Data Manajemen SW: dipotong sampai kemarin (H-1) karena Sleekflow belum punya data hari ini. */
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
            $service->forget(...$service->previousRange(...$range));
        }
    }

    public function render(ManagementMetricsService $service)
    {
        $range = $this->range();

        if (! $range) {
            return view('livewire.executive-overview', ['range' => null, 'trimmed' => false]);
        }

        $now = $service->collect(...$range);
        $prevRange = $service->previousRange(...$range);
        $prev = $service->collect(...$prevRange);
        $in = $now['inputs'];
        $pin = $prev['inputs'];

        // SPK closing = SPK terbit tanpa garansi -GR dan tanpa R&D (R&D dikurangi setelah ERP mengirim spk_rd_periode)
        $closing = fn (array $i) => isset($i['spk_non_gr']) ? $i['spk_non_gr'] - ($i['spk_rd'] ?? 0) : null;

        $mom = fn (?float $a, ?float $b, string $arah, bool $perDay) => $service->compareValues($a, $b, $arah, $perDay, $now['days'], $prev['days']);

        $cards = [
            'omset' => ['nilai' => $in['revenue'] ?? null, 'lalu' => $pin['revenue'] ?? null] + $mom($in['revenue'] ?? null, $pin['revenue'] ?? null, 'naik', true),
            'spk' => ['nilai' => $closing($in), 'lalu' => $closing($pin)] + $mom($closing($in), $closing($pin), 'naik', true),
            'roas' => ['nilai' => $now['values'][13], 'lalu' => $prev['values'][13]] + $mom($now['values'][13], $prev['values'][13], 'naik', false),
        ];

        $funnel = [
            ['label' => 'Chat masuk baru', 'nilai' => $in['chats'] ?? null, 'ket' => 'Sleekflow'],
            ['label' => 'Isi data diri', 'nilai' => $in['leads'] ?? null, 'ket' => 'Formulir customer di sistem pusat'],
            ['label' => 'SPK terbit', 'nilai' => $in['spk_non_gr'] ?? null, 'ket' => 'Tanpa SPK garansi -GR'],
            ['label' => 'Sepatu tiba di gudang', 'nilai' => $in['arrived'] ?? null, 'ket' => 'Sudah QC awal gudang'],
        ];

        return view('livewire.executive-overview', [
            'range' => $range,
            'prevRange' => $prevRange,
            'trimmed' => $range[1] !== $this->end,
            'days' => $now['days'],
            'cards' => $cards,
            'funnel' => $funnel,
            'intake' => $this->intake($in),
            'projection' => $this->projection($service, $range, $in['revenue'] ?? null, $now['days']),
            'chart' => $this->chart($service, $range),
            'sourceErrors' => $now['errors'] + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors']),
        ]);
    }

    /** SPK terbit per jenis: berbayar, garansi (-GR), R&D (RD-). */
    protected function intake(array $in): ?array
    {
        if (! isset($in['spk'], $in['spk_non_gr'], $in['spk_gr'])) {
            return null;
        }

        $rd = $in['spk_rd'] ?? null;

        return [
            'berbayar' => $in['spk_non_gr'] - ($rd ?? 0),
            'garansi' => $in['spk_gr'],
            'rd' => $rd,
            'total' => $in['spk'],
            'tiba' => $in['arrived'] ?? null,
        ];
    }

    /**
     * Proyeksi omset akhir bulan (laju harian × jumlah hari bulan), hanya kalau rentang dimulai tanggal 1
     * dan masih di dalam satu bulan yang belum selesai. Pembanding: omset bulan lalu penuh.
     */
    protected function projection(ManagementMetricsService $service, array $range, ?float $revenue, int $days): ?array
    {
        $start = Carbon::parse($range[0]);
        $end = Carbon::parse($range[1]);

        if ($revenue === null || $start->day !== 1 || ! $start->isSameMonth($end) || $end->isSameDay($end->copy()->endOfMonth())) {
            return null;
        }

        $lastMonth = $start->copy()->subMonthNoOverflow();

        return [
            'nilai' => $revenue / $days * $start->daysInMonth,
            'bulan_lalu' => $service->revenue($lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()),
            'nama_bulan_lalu' => $lastMonth->translatedFormat('F'),
        ];
    }

    /** Seri harian untuk grafik. Omset harian null = field ERP belum ada (grafik hanya biaya iklan). */
    protected function chart(ManagementMetricsService $service, array $range): array
    {
        try {
            $spend = $service->dailySpend(...$range);
        } catch (\Throwable) {
            $spend = [];
        }

        try {
            $revenue = $service->dailyRevenue(...$range);
        } catch (\Throwable) {
            $revenue = null;
        }

        $labels = $spendSeries = $revenueSeries = [];
        foreach (CarbonPeriod::create(...$range) as $day) {
            $key = $day->toDateString();
            $labels[] = $day->translatedFormat('j M');
            $spendSeries[] = round($spend[$key] ?? 0);
            $revenueSeries[] = $revenue === null ? null : round($revenue[$key] ?? 0);
        }

        return ['labels' => $labels, 'spend' => $spendSeries, 'revenue' => $revenue === null ? null : $revenueSeries];
    }
}
