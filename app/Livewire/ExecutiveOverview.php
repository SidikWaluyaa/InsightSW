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

    /** Tombol "i" (Admin): isi modal rumus untuk angka di Executive Overview. */
    public function showFormula(string $key, ManagementMetricsService $service): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! ($range = $this->range())) {
            return;
        }

        $info = $this->explain($key, $service, $range);

        if ($info) {
            $this->dispatch('show-formula', ...$info);
        }
    }

    /**
     * Rincian rumus per angka: sumber, definisi, langkah hitung dengan angka periode ini, dan cek silang.
     * Format mengikuti <x-ui.formula-modal> (sama dengan ManagementMetricsService::explain).
     */
    protected function explain(string $key, ManagementMetricsService $service, array $range): ?array
    {
        // Data yang sama persis dengan kartu: No. 13 (MER), 15 (chat), 20 (isi data diri) memakai rincian Data Manajemen SW
        $reuse = ['roas' => [13, 'ROAS (MER)'], 'funnel_1' => [15, 'Corong 1 · Chat masuk baru'], 'funnel_2' => [20, 'Corong 2 · Isi data diri']];

        if (isset($reuse[$key])) {
            [$no, $title] = $reuse[$key];
            $info = $service->explain($no, ...$range);
            $info['judul'] = $title;

            if ($key === 'funnel_2') {
                $in = $service->collect(...$range)['inputs'];
                $info['cek'][] = ['ok' => true, 'teks' => 'Persen di corong: isi data diri ÷ chat masuk = ' . $this->n($in['leads'] ?? 0) . ' ÷ ' . $this->n($in['chats'] ?? 0) . ' = ' . $this->pct($in['leads'] ?? null, $in['chats'] ?? null)];
            }

            return $info;
        }

        $now = $service->collect(...$range);
        [$ps, $pe] = $service->previousRange(...$range);
        $prev = $service->collect($ps, $pe);
        $in = $now['inputs'];
        $pin = $prev['inputs'];
        $days = $now['days'];
        $period = "start_date={$range[0]}&end_date={$range[1]}";
        $gudang = "API pusat GET /warehouse-summary?{$period}";
        $rd = $in['spk_rd'] ?? null;
        $closing = fn (array $i) => $i['spk_closing'] ?? null;
        $rp = fn ($v) => $v === null ? '–' : 'Rp ' . $this->n($v);
        $rdNote = $rd === null
            ? [['ok' => false, 'teks' => 'Field spk_rd_periode (SPK R&D, nomor RD-) belum dikirim sistem pusat; sementara R&D masih ikut terhitung.']]
            : [];

        $info = match ($key) {
            'omset' => [
                'judul' => 'Total Omset (Net)',
                'endpoint' => "API pusat GET /finance/dashboard?{$period}",
                'field' => 'data.metrics.omset_tagihan',
                'nilai' => $rp($in['revenue'] ?? null),
                'definisi' => 'Nilai jasa pada tagihan (invoice) yang dibuat di periode ini, sudah dikurangi diskon. Tagihan Batal dan tagihan '
                    . 'bernilai jasa 0 (garansi / rework) tidak dihitung. Ongkir tidak termasuk omset. Sama dengan omset di MER dan Data Manajemen SW.',
                'rumus_db' => "SUM(nilai jasa − diskon) dari invoices\nWHERE invoices.created_at BETWEEN :start AND :end\n  AND status <> 'Batal'\n  AND nilai jasa > 0\n-- ongkir (invoices.shipping_cost) dicatat terpisah: ongkir_tagihan",
                'langkah' => array_merge(
                    [['op' => '=', 'teks' => 'Diambil langsung dari sistem pusat (dihitung di database pusat)', 'rumus' => null, 'hasil' => $rp($in['revenue'] ?? null)]],
                    $this->momSteps($in['revenue'] ?? null, $pin['revenue'] ?? null, $days, $prev['days'], true, $rp),
                ),
                'cek' => array_merge(
                    isset($in['shipping']) ? [['ok' => true, 'teks' => 'Ongkir ' . $rp($in['shipping']) . ' tidak termasuk omset']] : [],
                    isset($in['invoices']) ? [['ok' => true, 'teks' => 'Dari ' . $this->n($in['invoices']) . ' tagihan berisi ' . $this->n($in['invoice_spk'] ?? 0) . ' SPK']] : [],
                    $this->momCek($service, $in['revenue'] ?? null, $pin['revenue'] ?? null, $days, $prev['days'], true, $ps, $pe, $rp),
                ),
            ],
            'spk' => [
                'judul' => 'SPK Closing',
                'endpoint' => $gudang,
                'field' => 'data.summary.spk_periode_non_gr' . ($rd === null ? '' : ' − spk_rd_periode'),
                'nilai' => $this->n($closing($in)) . ' pasang',
                'definisi' => 'SPK (1 SPK = 1 pasang) yang dibuat di periode ini, tanpa SPK klaim garansi (nomor berakhiran -GR) dan tanpa SPK riset (R&D, nomor diawali RD-).',
                'rumus_db' => "SELECT COUNT(*) FROM work_orders\nWHERE created_at BETWEEN :start AND :end\n  AND deleted_at IS NULL\n  AND NOT (spk_number LIKE '%-GR' OR spk_number LIKE '%-GR-%')\n  AND category_spk <> 'RD'   -- setelah field spk_rd_periode tersedia",
                'langkah' => array_merge(
                    [
                        ['op' => '−', 'teks' => 'Semua SPK terbit − SPK garansi -GR', 'rumus' => $this->n($in['spk'] ?? 0) . ' − ' . $this->n($in['spk_gr'] ?? 0), 'hasil' => $this->n($in['spk_non_gr'] ?? 0)],
                        ['op' => '−', 'teks' => 'Dikurangi SPK R&D', 'rumus' => $this->n($in['spk_non_gr'] ?? 0) . ' − ' . ($rd === null ? '(belum tersedia)' : $this->n($rd)), 'hasil' => $this->n($closing($in))],
                        ['op' => '÷', 'teks' => 'Rata-rata per hari', 'rumus' => $this->n($closing($in)) . ' ÷ ' . $days . ' hari', 'hasil' => $this->n(($closing($in) ?? 0) / $days, 1) . ' pasang/hari'],
                    ],
                    $this->momSteps($closing($in), $closing($pin), $days, $prev['days'], true, fn ($v) => $this->n($v, fmod($v, 1) ? 1 : 0) . ' pasang'),
                ),
                'cek' => array_merge(
                    isset($in['spk'], $in['spk_gr'], $in['spk_non_gr']) ? [['ok' => $in['spk'] - $in['spk_gr'] == $in['spk_non_gr'], 'teks' => 'total_incoming − spk_gr_periode = spk_periode_non_gr (' . $this->n($in['spk_non_gr']) . ')']] : [],
                    $rdNote,
                    $this->momCek($service, $closing($in), $closing($pin), $days, $prev['days'], true, $ps, $pe, fn ($v) => $this->n($v) . ' pasang'),
                ),
            ],
            'akurasi' => [
                'judul' => 'Tingkat Akurasi Data',
                'endpoint' => 'Belum ada',
                'field' => '–',
                'nilai' => 'Menyusul',
                'definisi' => 'Kecocokan data di sistem dengan kondisi lapangan (PRD: target ≥ 98%). Cara mencocokkan dan sumber datanya masih didiskusikan dengan Tim Data, jadi belum dihitung.',
                'rumus_db' => "Usulan PRD: (1 − selisih ÷ total) × 100%\n-- selisih dan total belum didefinisikan (data apa dibanding data apa)",
                'langkah' => [],
                'cek' => [],
            ],
            'funnel_3' => [
                'judul' => 'Corong 3 · SPK closing',
                'endpoint' => $gudang,
                'field' => 'data.summary.spk_periode_non_gr − spk_rd_periode',
                'nilai' => $this->n($in['spk_closing'] ?? null),
                'definisi' => 'SPK yang dibuat di periode ini tanpa SPK garansi -GR dan tanpa R&D (sama dengan kartu SPK Closing). Persen = SPK closing ÷ isi data diri. '
                    . 'Ringkasan di bawah corong: chat-to-SPK = SPK closing ÷ chat masuk, nilai deal = omset tagihan.',
                'rumus_db' => "SELECT COUNT(*) FROM work_orders\nWHERE created_at BETWEEN :start AND :end AND deleted_at IS NULL\n  AND NOT (spk_number LIKE '%-GR' OR spk_number LIKE '%-GR-%')\n  AND spk_number NOT LIKE 'RD-%'",
                'langkah' => [
                    ['op' => '−', 'teks' => 'Semua SPK terbit − garansi -GR − R&D', 'rumus' => $this->n($in['spk'] ?? 0) . ' − ' . $this->n($in['spk_gr'] ?? 0) . ' − ' . $this->n($rd ?? 0), 'hasil' => $this->n($in['spk_closing'] ?? null)],
                    ['op' => '÷', 'teks' => 'Persen di corong: SPK closing ÷ isi data diri', 'rumus' => $this->n($in['spk_closing'] ?? 0) . ' ÷ ' . $this->n($in['leads'] ?? 0), 'hasil' => $this->pct($in['spk_closing'] ?? null, $in['leads'] ?? null)],
                    ['op' => '÷', 'teks' => 'Chat-to-SPK keseluruhan: SPK closing ÷ chat masuk', 'rumus' => $this->n($in['spk_closing'] ?? 0) . ' ÷ ' . $this->n($in['chats'] ?? 0), 'hasil' => $this->pct($in['spk_closing'] ?? null, $in['chats'] ?? null)],
                ],
                'cek' => [['ok' => true, 'teks' => 'Satu isi data diri bisa menghasilkan beberapa SPK (beberapa pasang), jadi persen ini bisa mendekati atau melewati 100%.']],
            ],
            'funnel_4' => [
                'judul' => 'Corong 4 · Sepatu tiba di gudang',
                'endpoint' => $gudang,
                'field' => 'data.summary.spk_tiba_periode',
                'nilai' => $this->n($in['arrived'] ?? null),
                'definisi' => 'Dari SPK terbit (tanpa -GR) di periode ini, yang sepatunya sudah tiba dan dicek QC awal gudang. Masih termasuk SPK R&D yang sudah tiba (belum ada rinciannya), '
                    . 'jadi persennya sedikit lebih tinggi. Status dibaca per hari ini, jadi angka periode lama bisa bertambah.',
                'rumus_db' => "SELECT COUNT(*) FROM work_orders\nWHERE created_at BETWEEN :start AND :end AND deleted_at IS NULL\n  AND NOT (spk_number LIKE '%-GR' OR spk_number LIKE '%-GR-%')\n  AND warehouse_qc_at IS NOT NULL",
                'langkah' => [
                    ['op' => '÷', 'teks' => 'Persen di corong: sepatu tiba ÷ SPK closing', 'rumus' => $this->n($in['arrived'] ?? 0) . ' ÷ ' . $this->n($in['spk_closing'] ?? 0), 'hasil' => $this->pct($in['arrived'] ?? null, $in['spk_closing'] ?? null)],
                ],
                'cek' => isset($in['spk_non_gr'], $in['arrived'], $in['not_arrived']) ? [
                    ['ok' => $in['spk_non_gr'] - $in['arrived'] == $in['not_arrived'], 'teks' => 'SPK terbit − sepatu tiba = sepatu belum dikirim (' . $this->n($in['not_arrived']) . ')'],
                ] : [],
            ],
            'proyeksi' => $this->explainProjection($service, $range, $in['revenue'] ?? null, $days, $rp),
            'intake' => isset($in['fisik_berbayar'], $in['fisik_garansi'], $in['fisik_rd']) ? $this->explainFisik($in, $gudang) : [
                'judul' => 'SPK Terbit per Jenis',
                'endpoint' => $gudang,
                'field' => 'total_incoming, spk_gr_periode, spk_periode_non_gr, spk_rd_periode',
                'nilai' => $this->n($in['spk'] ?? null) . ' SPK',
                'definisi' => 'SPK yang dibuat di periode ini dibagi menurut nomor SPK: klaim garansi berakhiran -GR, riset (R&D) diawali RD-, sisanya berbayar. Persen = bagian ÷ total SPK terbit.',
                'rumus_db' => "Garansi : spk_number LIKE '%-GR' OR spk_number LIKE '%-GR-%'\nR&D     : category_spk = 'RD' (nomor RD-)\nBerbayar: spk_periode_non_gr − R&D",
                'langkah' => [
                    ['op' => '−', 'teks' => 'Berbayar = SPK tanpa -GR − SPK R&D', 'rumus' => $this->n($in['spk_non_gr'] ?? 0) . ' − ' . ($rd === null ? '(belum tersedia)' : $this->n($rd)), 'hasil' => $this->n($closing($in))],
                    ['op' => '=', 'teks' => 'Klaim garansi (-GR)', 'rumus' => null, 'hasil' => $this->n($in['spk_gr'] ?? null)],
                    ['op' => '=', 'teks' => 'Riset (R&D)', 'rumus' => null, 'hasil' => $rd === null ? 'menyusul' : $this->n($rd)],
                    ['op' => '+', 'teks' => 'Total = berbayar + garansi + R&D', 'rumus' => $this->n($closing($in)) . ' + ' . $this->n($in['spk_gr'] ?? 0) . ' + ' . $this->n($rd ?? 0), 'hasil' => $this->n(($closing($in) ?? 0) + ($in['spk_gr'] ?? 0) + ($rd ?? 0))],
                ],
                'cek' => array_merge(
                    isset($in['spk']) ? [['ok' => ($closing($in) ?? 0) + ($in['spk_gr'] ?? 0) + ($rd ?? 0) == $in['spk'], 'teks' => 'Jumlah ketiga jenis = total SPK terbit (total_incoming ' . $this->n($in['spk']) . ')']] : [],
                    $rdNote,
                    [['ok' => false, 'teks' => 'Rincian sepatu yang fisiknya tiba per jenis (intake_fisik_*) belum dikirim sistem pusat, jadi panel memakai SPK terbit. '
                        . 'Total sepatu tiba pada periode (total_qc_processed): ' . $this->n($in['fisik_total'] ?? null)]],
                ),
            ],
            default => null,
        };

        if (! $info) {
            return null;
        }

        foreach (array_merge($now['errors'], $prev['errors']) as $error) {
            $info['cek'][] = ['ok' => false, 'teks' => $error];
        }

        return [
            'judul' => $info['judul'],
            'sifat' => 'periode',
            'endpoint' => $info['endpoint'],
            'field' => $info['field'],
            'nilai' => $info['nilai'],
            'definisi' => $info['definisi'],
            'rumus_db' => $info['rumus_db'],
            'hitung' => $info['langkah'] ? ['langkah' => $info['langkah'], 'rincian' => []] : null,
            'cek' => $info['cek'],
            'label_nilai' => 'Nilai di Dashboard',
            'label_rumus' => 'Rumus',
            'sumber_rumus' => 'Acuan: PRD Master Blueprint v3.0 dan Kamus Metrik v2.0. Periode ' . $range[0] . ' s/d ' . $range[1] . '.',
        ];
    }

    /** Rincian panel gudang mode fisik (Kamus No. 32 Intake Fisik per jenis). */
    protected function explainFisik(array $in, string $gudang): array
    {
        $sum = $in['fisik_berbayar'] + $in['fisik_garansi'] + $in['fisik_rd'];

        return [
            'judul' => 'Sepatu Masuk per Jenis (Fisik Tiba)',
            'endpoint' => $gudang,
            'field' => 'intake_fisik_berbayar, intake_fisik_garansi, intake_fisik_rd, total_qc_processed',
            'nilai' => $this->n($sum) . ' pasang',
            'definisi' => 'Sepatu yang benar-benar tiba di gudang pada periode ini (Kamus No. 32 Intake Fisik), menurut tanggal tiba, dibagi menurut nomor SPK: '
                . 'klaim garansi -GR, riset R&D (RD-), sisanya berbayar. Berbeda dari SPK terbit, yang memakai tanggal SPK dibuat.',
            'rumus_db' => "Tanggal tiba sepatu di gudang di dalam periode\nGaransi : spk_number LIKE '%-GR' OR '%-GR-%'\nR&D     : spk_number LIKE 'RD-%'\nBerbayar: sisanya",
            'langkah' => [
                ['op' => '+', 'teks' => 'Berbayar + garansi + R&D', 'rumus' => $this->n($in['fisik_berbayar']) . ' + ' . $this->n($in['fisik_garansi']) . ' + ' . $this->n($in['fisik_rd']), 'hasil' => $this->n($sum)],
            ],
            'cek' => isset($in['fisik_total']) ? [[
                'ok' => $sum == $in['fisik_total'],
                'teks' => 'Total per jenis vs total sepatu tiba (total_qc_processed ' . $this->n($in['fisik_total']) . '). Beda bisa terjadi kalau sepatu garansi dicatat tiba tanpa QC gudang.',
            ]] : [],
        ];
    }

    protected function explainProjection(ManagementMetricsService $service, array $range, ?float $revenue, int $days, \Closure $rp): array
    {
        $projection = $this->projection($service, $range, $revenue, $days);
        $daysInMonth = Carbon::parse($range[0])->daysInMonth;

        return [
            'judul' => 'Proyeksi Omset Akhir Bulan',
            'endpoint' => 'API pusat GET /finance/dashboard (omset_tagihan rentang ini dan bulan lalu penuh)',
            'field' => 'data.metrics.omset_tagihan',
            'nilai' => $rp($projection['nilai'] ?? null),
            'definisi' => 'Perkiraan omset sebulan penuh kalau laju harian sejauh ini berlanjut. Hanya muncul kalau rentang dimulai tanggal 1 dan bulannya belum selesai.',
            'rumus_db' => "(omset tanggal 1 s/d kemarin ÷ jumlah hari berjalan) × jumlah hari dalam bulan",
            'langkah' => $projection ? [
                ['op' => '÷', 'teks' => 'Omset ÷ hari berjalan = laju per hari', 'rumus' => $rp($revenue) . ' ÷ ' . $days . ' hari', 'hasil' => $rp($revenue / $days)],
                ['op' => '×', 'teks' => 'Laju per hari × hari dalam bulan', 'rumus' => $rp($revenue / $days) . ' × ' . $daysInMonth . ' hari', 'hasil' => $rp($projection['nilai'])],
            ] : [],
            'cek' => $projection && $projection['bulan_lalu'] ? [[
                'ok' => $projection['nilai'] >= $projection['bulan_lalu'],
                'teks' => 'Omset ' . $projection['nama_bulan_lalu'] . ' penuh: ' . $rp($projection['bulan_lalu']) . '; proyeksi ' . ($projection['nilai'] >= $projection['bulan_lalu'] ? 'di atas' : 'di bawah') . ' bulan lalu ' . $this->n(abs($projection['nilai'] / $projection['bulan_lalu'] * 100 - 100), 1) . '%',
            ]] : [],
        ];
    }

    /** Langkah perbandingan MoM untuk modal: (per hari) bulan ini vs bulan lalu → perubahan. */
    protected function momSteps(?float $now, ?float $prev, int $days, int $prevDays, bool $perDay, \Closure $fmt): array
    {
        if ($now === null || ! $prev) {
            return [];
        }

        $a = $perDay ? $now / $days : $now;
        $b = $perDay ? $prev / $prevDays : $prev;
        $steps = [];

        if ($perDay) {
            $steps[] = ['op' => '÷', 'teks' => 'Periode ini per hari', 'rumus' => $fmt($now) . ' ÷ ' . $days . ' hari', 'hasil' => $fmt($a)];
            $steps[] = ['op' => '÷', 'teks' => 'Bulan lalu per hari', 'rumus' => $fmt($prev) . ' ÷ ' . $prevDays . ' hari', 'hasil' => $fmt($b)];
        }

        $steps[] = ['op' => '÷', 'teks' => 'Perubahan dibanding bulan lalu: (ini − lalu) ÷ lalu × 100', 'rumus' => '(' . $fmt($a) . ' − ' . $fmt($b) . ') ÷ ' . $fmt($b) . ' × 100', 'hasil' => (($a - $b) >= 0 ? '+' : '') . $this->n(($a - $b) / $b * 100, 1) . '%'];

        return $steps;
    }

    /** Cek lampu MoM untuk modal. */
    protected function momCek(ManagementMetricsService $service, ?float $now, ?float $prev, int $days, int $prevDays, bool $perDay, string $ps, string $pe, \Closure $fmt): array
    {
        $mom = $service->compareValues($now, $prev, 'naik', $perDay, $days, $prevDays);

        return [[
            'ok' => $mom['status'] !== 'merah',
            'teks' => "Bulan lalu ({$ps} s/d {$pe}): " . $fmt($prev) . ($mom['status'] ? '; lampu ' . $mom['status'] . ' (kuning = lebih buruk sampai ' . config('management_metrics.mom.batas_kuning') . '%)' : ''),
        ]];
    }

    protected function n(?float $v, int $dec = 0): string
    {
        return $v === null ? '–' : number_format($v, $dec, ',', '.');
    }

    protected function pct(?float $a, ?float $b): string
    {
        return $a === null || ! $b ? '–' : number_format($a / $b * 100, 1, ',', '.') . '%';
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

        // SPK closing = SPK terbit tanpa garansi -GR dan tanpa R&D (dihitung di ManagementMetricsService::collect)
        $closing = fn (array $i) => $i['spk_closing'] ?? null;

        $mom = fn (?float $a, ?float $b, string $arah, bool $perDay) => $service->compareValues($a, $b, $arah, $perDay, $now['days'], $prev['days']);

        $cards = [
            'omset' => ['nilai' => $in['revenue'] ?? null, 'lalu' => $pin['revenue'] ?? null] + $mom($in['revenue'] ?? null, $pin['revenue'] ?? null, 'naik', true),
            'spk' => ['nilai' => $closing($in), 'lalu' => $closing($pin)] + $mom($closing($in), $closing($pin), 'naik', true),
            'roas' => ['nilai' => $now['values'][13], 'lalu' => $prev['values'][13]] + $mom($now['values'][13], $prev['values'][13], 'naik', false),
        ];

        $funnel = [
            ['label' => 'Chat masuk baru', 'nilai' => $in['chats'] ?? null, 'ket' => 'Sleekflow'],
            ['label' => 'Isi data diri', 'nilai' => $in['leads'] ?? null, 'ket' => 'Formulir customer di sistem pusat'],
            ['label' => 'SPK closing', 'nilai' => $in['spk_closing'] ?? null, 'ket' => 'Tanpa garansi -GR dan R&D'],
            ['label' => 'Sepatu tiba di gudang', 'nilai' => $in['arrived'] ?? null, 'ket' => 'Sudah QC awal gudang'],
        ];

        return view('livewire.executive-overview', [
            'range' => $range,
            'prevRange' => $prevRange,
            'trimmed' => $range[1] !== $this->end,
            'days' => $now['days'],
            'cards' => $cards,
            'funnel' => $funnel,
            // Catatan di bawah tahap SPK (PRD): chat-to-SPK keseluruhan dan nilai deal (omset tagihan)
            'funnelSummary' => [
                'chat_to_spk' => isset($in['spk_closing'], $in['chats']) && $in['chats'] > 0 ? $in['spk_closing'] / $in['chats'] * 100 : null,
                'omset' => $in['revenue'] ?? null,
            ],
            'intake' => $this->intake($in),
            'projection' => $this->projection($service, $range, $in['revenue'] ?? null, $now['days']),
            'chart' => $this->chart($service, $range),
            'sourceErrors' => $now['errors'] + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors']),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
        ]);
    }

    /**
     * Status sepatu masuk per jenis (PRD Alur 3 kanan). Mode "fisik" (Kamus No. 32, sepatu yang benar-benar tiba)
     * dipakai bila ERP sudah mengirim intake_fisik_*; sebelum itu jatuh ke SPK terbit per jenis.
     */
    protected function intake(array $in): ?array
    {
        if (isset($in['fisik_berbayar'], $in['fisik_garansi'], $in['fisik_rd'])) {
            return [
                'mode' => 'fisik',
                'berbayar' => $in['fisik_berbayar'],
                'garansi' => $in['fisik_garansi'],
                'rd' => $in['fisik_rd'],
                'total' => $in['fisik_berbayar'] + $in['fisik_garansi'] + $in['fisik_rd'],
                'tiba' => $in['fisik_total'] ?? null,
            ];
        }

        if (! isset($in['spk'], $in['spk_non_gr'], $in['spk_gr'])) {
            return null;
        }

        $rd = $in['spk_rd'] ?? null;

        return [
            'mode' => 'spk',
            'berbayar' => $in['spk_non_gr'] - ($rd ?? 0),
            'garansi' => $in['spk_gr'],
            'rd' => $rd,
            'total' => $in['spk'],
            'tiba' => $in['fisik_total'] ?? null,
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
