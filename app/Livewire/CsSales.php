<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use App\Services\CsSalesReportService;
use App\Services\ManagementMetricsService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if (in_array($no, CsSalesReportService::CARDS, true)) {
            $this->dispatch('show-formula', ...$service->explain($no, $this->startDate, $this->endDate));
        }
    }

    /** Unduh leaderboard dan tabel harian sebagai CSV (dibuka di Excel / Google Sheets). */
    public function exportCsv(CsSalesReportService $report): StreamedResponse
    {
        $leaderboard = $report->leaderboard($this->startDate, $this->endDate);
        $daily = $report->daily($this->startDate, $this->endDate);
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

    public function render(CsSalesReportService $report)
    {
        $summary = $report->summary($this->startDate, $this->endDate);

        return view('livewire.cs-sales', $summary + [
            'leaderboard' => $report->leaderboard($this->startDate, $this->endDate),
            'daily' => $report->daily($this->startDate, $this->endDate),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
