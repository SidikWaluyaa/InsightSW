<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use App\Services\ManagementMetricsService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Penjualan CS (PRD halaman 3): 4 kartu penjualan dengan lampu MoM, leaderboard per CS, dan tabel harian.
 * Definisi angka sama dengan Dashboard dan Data Manajemen SW (Kamus Metrik v2.0).
 */
#[Layout('layouts.app')]
class CsSales extends Component
{
    use WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    /** Nomor data di config/management_metrics.php untuk 4 kartu. */
    protected const CARDS = [20, 27, 29, 26];

    public function mount(): void
    {
        $max = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $max->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $max->toDateString()
        );
    }

    /** % chat jadi SPK butuh Sleekflow, yang baru lengkap besok, jadi paling akhir kemarin (H-1). */
    protected function maxSelectableDate(): Carbon
    {
        return now()->subDay()->startOfDay();
    }

    public function refreshData(ManagementMetricsService $service): void
    {
        $service->forget($this->startDate, $this->endDate);
        $service->forget(...$service->previousRange($this->startDate, $this->endDate));
    }

    public function showFormula(int $no, ManagementMetricsService $service): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (in_array($no, self::CARDS, true)) {
            $this->dispatch('show-formula', ...$service->explain($no, $this->startDate, $this->endDate));
        }
    }

    /** Unduh leaderboard dan tabel harian sebagai CSV (dibuka di Excel / Google Sheets). */
    public function exportCsv(ManagementMetricsService $service): StreamedResponse
    {
        $leaderboard = $this->leaderboard($service);
        $daily = $this->daily($service);
        $name = "penjualan-cs_{$this->startDate}_{$this->endDate}.csv";

        return response()->streamDownload(function () use ($leaderboard, $daily) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            $put = fn (array $row) => fputcsv($out, $row, ',', '"', '');

            $put(["Penjualan CS {$this->startDate} s/d {$this->endDate}"]);
            $put([]);
            $put(['Peringkat', 'CS', 'Isi data diri', 'SPK closing', 'Tagihan', 'Omset tagihan', 'Sepatu per tagihan', 'Nilai per tagihan']);
            foreach ($leaderboard ?? [] as $i => $row) {
                $put([$i + 1, $row['nama'], $row['leads'], $row['spk'], $row['tagihan'], $row['omset'], $row['per_tagihan'], $row['nilai_tagihan']]);
            }

            $put([]);
            $put(['Tanggal', 'Isi data diri', 'SPK closing', 'Tagihan', 'Omset tagihan', 'Nilai per tagihan', 'Biaya iklan', 'Biaya per SPK']);
            foreach ($daily['rows'] as $row) {
                $put([$row['tanggal'], $row['leads'], $row['spk'], $row['tagihan'], $row['omset'], $row['nilai_tagihan'], $row['biaya'], $row['biaya_spk']]);
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Leaderboard per CS, diurutkan omset terbesar. null = field per_cs_kamus belum dikirim ERP.
     */
    protected function leaderboard(ManagementMetricsService $service): ?array
    {
        try {
            $rows = $service->csBreakdown($this->startDate, $this->endDate)['per_cs'];
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
    protected function daily(ManagementMetricsService $service): array
    {
        $harian = rescue(fn () => $service->csBreakdown($this->startDate, $this->endDate)['harian'], null, false);
        $omset = rescue(fn () => $service->dailyRevenue($this->startDate, $this->endDate), null, false);
        $biaya = rescue(fn () => $service->dailySpend($this->startDate, $this->endDate), [], false);

        $rows = [];
        foreach (CarbonPeriod::create($this->startDate, $this->endDate) as $day) {
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

    public function render(ManagementMetricsService $service)
    {
        $now = $service->collect($this->startDate, $this->endDate);
        $prevRange = $service->previousRange($this->startDate, $this->endDate);
        $prev = $service->collect(...$prevRange);
        $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];

        $cards = [];
        foreach (self::CARDS as $no) {
            $mom = $service->compare($no, $now['values'][$no], $prev['values'][$no], $now['days'], $prev['days']);
            $cards[$no] = [
                'nama' => config("management_metrics.data.{$no}.nama"),
                'icon' => config("management_metrics.data.{$no}.icon"),
                'singkat' => config("management_metrics.data.{$no}.singkat"),
                'nilai' => $service->format($no, $now['values'][$no]),
                'lalu' => $service->format($no, $prev['values'][$no]),
                'badge' => $mom['perubahan'] === null ? null : ($mom['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($mom['perubahan']), 1, ',', '.') . '% vs bulan lalu',
                'tone' => $tones[$mom['status']] ?? 'slate',
            ];
        }

        return view('livewire.cs-sales', [
            'cards' => $cards,
            'perPasang' => $service->format(28, $now['values'][28]),
            'prevRange' => $prevRange,
            'leaderboard' => $this->leaderboard($service),
            'daily' => $this->daily($service),
            'sourceErrors' => $now['errors'] + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors']),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
