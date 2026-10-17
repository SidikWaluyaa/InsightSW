<?php

namespace App\Services;

use Carbon\CarbonPeriod;
use Throwable;

/**
 * Data halaman Penjualan CS (peringkat per CS dan rincian harian), dipakai halaman Livewire, ekspor CSV, dan PDF
 * supaya ketiganya selalu menampilkan angka yang sama.
 */
class CsSalesReportService
{
    /** Nomor data di config/management_metrics.php untuk 4 kartu. */
    public const CARDS = [20, 27, 29, 26];

    public function __construct(protected ManagementMetricsService $metrics)
    {
    }

    /**
     * 4 kartu penjualan dengan perbandingan bulan lalu (MoM), plus rentang pembanding dan error sumber data.
     *
     * @return array{cards: array, perPasang: string, prevRange: array, sourceErrors: array}
     */
    public function summary(string $start, string $end): array
    {
        $now = $this->metrics->collect($start, $end);
        $prevRange = $this->metrics->previousRange($start, $end);
        $prev = $this->metrics->collect(...$prevRange);
        $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];

        $cards = [];
        foreach (self::CARDS as $no) {
            $mom = $this->metrics->compare($no, $now['values'][$no], $prev['values'][$no], $now['days'], $prev['days']);
            $cards[$no] = [
                'nama' => config("management_metrics.data.{$no}.nama"),
                'icon' => config("management_metrics.data.{$no}.icon"),
                'singkat' => config("management_metrics.data.{$no}.singkat"),
                'nilai' => $this->metrics->format($no, $now['values'][$no]),
                'lalu' => $this->metrics->format($no, $prev['values'][$no]),
                'badge' => $mom['perubahan'] === null ? null : ($mom['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($mom['perubahan']), 1, ',', '.') . '% vs bulan lalu',
                'tone' => $tones[$mom['status']] ?? 'slate',
                'status' => $mom['status'],
            ];
        }

        return [
            'cards' => $cards,
            'perPasang' => $this->metrics->format(28, $now['values'][28]),
            'prevRange' => $prevRange,
            'sourceErrors' => $now['errors'] + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors']),
        ];
    }

    /**
     * Leaderboard per CS, diurutkan omset terbesar. null = field per_cs_kamus belum dikirim ERP.
     */
    public function leaderboard(string $start, string $end): ?array
    {
        try {
            $rows = $this->metrics->csBreakdown($start, $end)['per_cs'];
        } catch (Throwable) {
            return null;
        }

        if ($rows === null) {
            return null;
        }

        return collect($rows)
            ->map(fn ($r) => [
                'nama' => $r['cs_name'] ?? 'Tanpa CS',
                'leads' => (int) ($r['leads'] ?? 0),
                'spk' => (int) ($r['spk_closing'] ?? 0),
                'tagihan' => (int) ($r['jumlah_tagihan'] ?? 0),
                'omset' => (float) ($r['omset_tagihan'] ?? 0),
                'per_tagihan' => ($r['jumlah_tagihan'] ?? 0) > 0 ? round(($r['jumlah_spk_tagihan'] ?? 0) / $r['jumlah_tagihan'], 2) : null,
                'nilai_tagihan' => ($r['jumlah_tagihan'] ?? 0) > 0 ? round(($r['omset_tagihan'] ?? 0) / $r['jumlah_tagihan']) : null,
            ])
            ->filter(fn ($r) => $r['leads'] + $r['spk'] + $r['tagihan'] > 0)
            ->sortByDesc('omset')
            ->values()
            ->all();
    }

    /**
     * Tabel harian (tanggal terbaru di atas). Omset dan biaya iklan sudah tersedia; kolom lain menunggu harian_kamus.
     *
     * @return array{rows: array, lengkap: bool}
     */
    public function daily(string $start, string $end): array
    {
        $harian = rescue(fn () => $this->metrics->csBreakdown($start, $end)['harian'], null, false);
        $omset = rescue(fn () => $this->metrics->dailyRevenue($start, $end), null, false);
        $biaya = rescue(fn () => $this->metrics->dailySpend($start, $end), [], false);

        $rows = [];
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $h = $harian[$key] ?? null;
            $revenue = $h['omset_tagihan'] ?? $omset[$key] ?? null;
            $invoices = $h['jumlah_tagihan'] ?? null;
            $spk = $h['spk_closing'] ?? null;
            $spend = $biaya[$key] ?? null;

            $rows[] = [
                'tanggal' => $key,
                'leads' => $h['leads'] ?? null,
                'spk' => $spk,
                'tagihan' => $invoices,
                'omset' => $revenue,
                'nilai_tagihan' => $invoices ? round($revenue / $invoices) : null,
                'biaya' => $spend === null ? null : round($spend),
                'biaya_spk' => $spk && $spend !== null ? round($spend / $spk) : null,
            ];
        }

        return ['rows' => array_reverse($rows), 'lengkap' => $harian !== null];
    }
}
