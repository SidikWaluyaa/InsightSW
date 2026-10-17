<?php

namespace App\Services;

use App\Models\SleekflowDailyAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Data Manajemen SW (config/management_metrics.php): menggabungkan Meta, Sleekflow, dan API pusat
 * untuk satu rentang tanggal, lalu menghitung tiap data beserta rincian langkah hitungnya.
 */
class ManagementMetricsService
{
    protected const GRAPH_URL = 'https://graph.facebook.com/v19.0';

    protected const CACHE_MINUTES = 30;

    /** Aksi Meta untuk "chat dari iklan" (sama dengan yang dibaca dokumen Tim Data). */
    protected const CHAT_ACTION = 'onsite_conversion.messaging_conversation_started_7d';

    /** Nama input untuk teks rincian. */
    protected const INPUT_LABELS = [
        'spend' => 'Biaya iklan',
        'impressions' => 'Jumlah tayang',
        'reach' => 'Jangkauan akun',
        'link_clicks' => 'Jumlah klik link',
        'ad_chats' => 'Jumlah chat dari iklan',
        'chats' => 'Total chat masuk baru',
        'purchase_value' => 'Nilai pembelian pixel Meta',
        'spk' => 'Semua SPK terbit',
        'spk_closing' => 'SPK closing (tanpa -GR dan R&D)',
        'spk_non_gr' => 'SPK terbit tanpa -GR',
        'not_arrived' => 'Sepatu tidak dikirim',
        'arrived' => 'Sepatu tiba',
        'leads' => 'Jumlah isi data diri',
        'not_leads' => 'Chat yang tidak isi data diri',
        'invoices' => 'Jumlah tagihan',
        'invoice_spk' => 'SPK di tagihan',
        'revenue' => 'Omset tagihan',
    ];

    /** Input yang ditampilkan sebagai rupiah di rincian. */
    protected const RUPIAH_INPUTS = ['spend', 'purchase_value', 'revenue'];

    /**
     * Ambil semua input dan hitung nilainya.
     *
     * @return array{inputs: array, values: array<int, float|null>, errors: array<string, string>}
     */
    public function collect(string $start, string $end): array
    {
        $inputs = [];
        $errors = [];

        try {
            $inputs += $this->metaTotals($start, $end);
        } catch (Throwable $e) {
            $errors['meta'] = 'Data Meta gagal diambil: ' . $e->getMessage();
        }

        $inputs['chats'] = (int) $this->sleekflowQuery($start, $end)->sum('number_of_new_enquires');
        $inputs += $this->responseTimes($start, $end);

        $sources = [
            'gudang' => ['Data SPK dari sistem pusat', fn () => $this->warehouseCounts($start, $end)],
            'leads' => ['Data isi data diri dari sistem pusat', fn () => $this->leadCounts($start, $end)],
            'tagihan' => ['Data tagihan dari sistem pusat', fn () => $this->invoiceCounts($start, $end)],
        ];

        foreach ($sources as $key => [$label, $fetch]) {
            try {
                $inputs += $fetch();
            } catch (Throwable $e) {
                $errors[$key] = $label . ' gagal diambil: ' . $e->getMessage();
            }
        }

        // SPK closing = sama dengan kartu SPK Closing di Dashboard: tanpa garansi -GR dan tanpa R&D (nomor RD-)
        if (isset($inputs['spk_non_gr'])) {
            $inputs['spk_closing'] = $inputs['spk_non_gr'] - ($inputs['spk_rd'] ?? 0);
        }

        if (isset($inputs['chats'], $inputs['leads'])) {
            $inputs['not_leads'] = max($inputs['chats'] - $inputs['leads'], 0);
        }

        $values = [];
        foreach (array_keys(config('management_metrics.data')) as $no) {
            $values[$no] = $this->compute($no, $inputs);
        }

        return ['inputs' => $inputs, 'values' => $values, 'errors' => $errors, 'days' => $this->days($start, $end)];
    }

    /**
     * Nilai satu data dari input. null = input belum ada atau pembagi nol.
     */
    public function compute(int $no, array $in): ?float
    {
        $div = fn ($a, $b) => isset($in[$a], $in[$b]) && $in[$b] > 0 ? $in[$a] / $in[$b] : null;
        $pct = fn ($a, $b) => ($v = $div($a, $b)) === null ? null : $v * 100;

        return match ($no) {
            1 => $in['spend'] ?? null,
            2 => $in['impressions'] ?? null,
            3 => $in['reach'] ?? null,
            4 => $div('impressions', 'reach'),
            5 => ($v = $div('spend', 'impressions')) === null ? null : $v * 1000,
            6 => $in['link_clicks'] ?? null,
            7 => ($v = $div('link_clicks', 'impressions')) === null ? null : $v * 100,
            8 => $div('spend', 'link_clicks'),
            9 => $in['ad_chats'] ?? null,
            10 => $div('spend', 'ad_chats'),
            11 => $pct('ad_chats', 'link_clicks'),
            12 => $div('purchase_value', 'spend'),
            // No. 14 sementara = MER: semua omset dianggap dari iklan sampai SPK ditandai sumbernya
            13, 14 => $div('revenue', 'spend'),
            15 => $in['chats'] ?? null,
            16 => $pct('ad_chats', 'chats'),
            17 => $in['resp_first'] ?? null,
            18 => $in['resp_all'] ?? null,
            19 => $pct('not_leads', 'chats'),
            20 => $in['leads'] ?? null,
            21 => $pct('leads', 'chats'),
            22 => $in['spk_closing'] ?? null,
            23 => $in['not_arrived'] ?? null,
            24 => $pct('not_arrived', 'spk_non_gr'),
            25 => $in['invoices'] ?? null,
            26 => $div('invoice_spk', 'invoices'),
            27 => $pct('spk_closing', 'chats'),
            28 => $div('revenue', 'invoice_spk'),
            29 => $div('revenue', 'invoices'),
            30 => $div('spend', 'spk_closing'),
            31 => $div('spend', 'arrived'),
            default => null,
        };
    }

    /**
     * Status target sehat: true = sesuai, false = di luar target, null = tidak ada target / belum ada nilai.
     * Target `per_hari` dibandingkan dengan nilai dibagi jumlah hari kalender rentang.
     */
    public function meetsTarget(int $no, ?float $value, int $days = 1): ?bool
    {
        $target = config("management_metrics.data.{$no}.target");

        if (! $target || $value === null) {
            return null;
        }

        if (! empty($target['per_hari'])) {
            $value /= max($days, 1);
        }

        return match (true) {
            isset($target['lt']) => $value < $target['lt'],
            isset($target['gt']) => $value > $target['gt'],
            isset($target['antara']) => $value >= $target['antara'][0] && $value <= $target['antara'][1],
            default => null,
        };
    }

    /**
     * Rentang pembanding bulan lalu (MoM). Rentang satu bulan penuh dibandingkan dengan bulan lalu penuh;
     * selain itu tanggalnya digeser satu bulan (1–17 Okt → 1–17 Sep; 31 Okt → 30 Sep).
     *
     * @return array{0: string, 1: string}
     */
    public function previousRange(string $start, string $end): array
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);

        if ($s->isSameDay($s->copy()->startOfMonth()) && $e->isSameDay($e->copy()->endOfMonth())) {
            return [$s->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $e->copy()->startOfMonth()->subMonthNoOverflow()->endOfMonth()->toDateString()];
        }

        return [$s->copy()->subMonthNoOverflow()->toDateString(), $e->copy()->subMonthNoOverflow()->toDateString()];
    }

    /**
     * Status lampu dibanding bulan lalu (kesepakatan Tim Data: MoM).
     * hijau = sama / lebih baik, kuning = lebih buruk sampai `batas_kuning` %, merah = lebih buruk dari itu.
     * Data bertipe jumlah dibandingkan per hari supaya rentang dengan jumlah hari berbeda tetap adil.
     * Data tanpa arah (mis. biaya iklan) atau tanpa nilai bulan lalu → status null, lalu kartu memakai target Kamus.
     *
     * @return array{perubahan: float|null, status: string|null}
     */
    public function compare(int $no, ?float $now, ?float $prev, int $days, int $prevDays): array
    {
        return $this->compareValues(
            $now, $prev,
            config("management_metrics.mom.arah.{$no}"),
            in_array($no, config('management_metrics.mom.jumlah'), true),
            $days, $prevDays
        );
    }

    /**
     * Inti perbandingan MoM untuk nilai apa saja (dipakai juga kartu utama Dashboard).
     *
     * @param  string|null  $arah  naik | turun | null (tanpa lampu)
     * @param  bool  $perDay  bandingkan nilai per hari (untuk data jumlah)
     * @return array{perubahan: float|null, status: string|null}
     */
    public function compareValues(?float $now, ?float $prev, ?string $arah, bool $perDay, int $days, int $prevDays): array
    {
        if ($now === null || $prev === null || $prev == 0) {
            return ['perubahan' => null, 'status' => null];
        }

        if ($perDay) {
            $now /= max($days, 1);
            $prev /= max($prevDays, 1);
        }

        $change = ($now - $prev) / abs($prev) * 100;

        if (! $arah) {
            return ['perubahan' => $change, 'status' => null];
        }

        $worse = $arah === 'naik' ? -$change : $change;

        return ['perubahan' => $change, 'status' => match (true) {
            $worse <= 0 => 'hijau',
            $worse <= config('management_metrics.mom.batas_kuning') => 'kuning',
            default => 'merah',
        }];
    }

    /**
     * Omset tagihan per tanggal (Y-m-d => rupiah) dari finance/dashboard chart_data.omset_tagihan_harian.
     * null = field belum dikirim ERP.
     */
    public function dailyRevenue(string $start, string $end): ?array
    {
        return Cache::remember("mgmt_inv_daily_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            $daily = $this->pusat('finance/dashboard', $start, $end, 'chart_data')['omset_tagihan_harian'] ?? null;

            return is_array($daily) ? array_map('floatval', $daily) : null;
        });
    }

    /**
     * Rincian penjualan CS dari dashboard-summary dengan definisi Kamus (field baru ERP, opsional):
     * - per_cs_kamus : [{cs_id, cs_name, leads, spk_closing, jumlah_tagihan, jumlah_spk_tagihan, omset_tagihan}]
     * - harian_kamus : {Y-m-d: {leads, spk_closing, jumlah_tagihan, jumlah_spk_tagihan, omset_tagihan}}
     * Bagian yang belum dikirim ERP bernilai null. per_cs lama tidak dipakai (closing memakai updated_at).
     *
     * @return array{per_cs: array|null, harian: array|null}
     */
    public function csBreakdown(string $start, string $end): array
    {
        return Cache::remember("mgmt_cs_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            $response = Http::timeout(60)
                ->withHeaders(['X-API-KEY' => config('services.dashboard.key'), 'Accept' => 'application/json'])
                ->get(rtrim(config('services.dashboard.base_url'), '/') . '/dashboard-summary', ['start_date' => $start, 'end_date' => $end]);

            if ($response->failed()) {
                throw new \RuntimeException('dashboard-summary HTTP ' . $response->status());
            }

            $perCs = $response->json('data.per_cs_kamus');
            $daily = $response->json('data.harian_kamus');

            return [
                'per_cs' => is_array($perCs) ? $perCs : null,
                'harian' => is_array($daily) ? $daily : null,
            ];
        });
    }

    /** Biaya iklan Meta per tanggal (Y-m-d => rupiah, tanpa pajak). */
    public function dailySpend(string $start, string $end): array
    {
        return array_map(fn ($row) => $row['spend'], $this->metaDaily($start, $end));
    }

    /** Omset tagihan satu rentang saja (untuk pembanding proyeksi akhir bulan). */
    public function revenue(string $start, string $end): ?float
    {
        try {
            return $this->invoiceCounts($start, $end)['revenue'];
        } catch (Throwable) {
            return null;
        }
    }

    public function format(int $no, ?float $value): string
    {
        if ($value === null) {
            return '–';
        }

        return match (config("management_metrics.data.{$no}.format")) {
            'rp' => 'Rp ' . number_format($value, 0, ',', '.'),
            'persen' => number_format($value, 2, ',', '.') . '%',
            'kali' => number_format($value, 2, ',', '.') . 'x',
            'pasang' => number_format($value, 2, ',', '.') . ' pasang',
            'durasi' => $this->duration($value),
            default => number_format($value, 0, ',', '.'),
        };
    }

    /** Detik → jj:mm:dd, dibulatkan ke bawah seperti SEC_TO_TIME di halaman Analitik Sleekflow. */
    protected function duration(float $seconds): string
    {
        $s = (int) floor($seconds);

        return sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
    }

    public function days(string $start, string $end): int
    {
        return (int) Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
    }

    /**
     * Isi modal info rumus (tombol "i"): definisi, rumus, langkah hitung dengan angka periode ini, nilai per hari.
     */
    public function explain(int $no, string $start, string $end): array
    {
        $def = config("management_metrics.data.{$no}");
        $data = $this->collect($start, $end);
        $in = $data['inputs'];
        $value = $data['values'][$no];

        $n = fn ($v, $dec = 0) => number_format((float) $v, $dec, ',', '.');
        $rp = fn ($v) => 'Rp ' . $n($v);
        $show = fn (string $key) => ! isset($in[$key]) ? '–' : (in_array($key, self::RUPIAH_INPUTS, true) ? $rp($in[$key]) : $n($in[$key]));
        $result = $this->format($no, $value);

        // Langkah pembagian umum: a ÷ b (× pengali) = hasil
        $ratio = function (string $a, string $b, int $multiplier = 1) use ($in, $show, $n, $result) {
            $steps = [[
                'op' => '÷',
                'teks' => self::INPUT_LABELS[$a] . ' ÷ ' . self::INPUT_LABELS[$b],
                'rumus' => $show($a) . ' ÷ ' . $show($b),
                'hasil' => isset($in[$a], $in[$b]) && $in[$b] > 0 ? $n($in[$a] / $in[$b], 6) : 'tidak bisa dihitung (pembagi 0 / kosong)',
            ]];

            if ($multiplier !== 1 && isset($in[$a], $in[$b]) && $in[$b] > 0) {
                $steps[] = [
                    'op' => '×',
                    'teks' => $multiplier === 100 ? 'Kali 100 supaya jadi persen' : 'Kali ' . $n($multiplier) . ' (per seribu tayangan)',
                    'rumus' => $n($in[$a] / $in[$b], 6) . ' × ' . $n($multiplier),
                    'hasil' => $n($in[$a] / $in[$b] * $multiplier, 4),
                ];
            }

            $steps[] = ['op' => '=', 'teks' => 'Nilai di kartu (dibulatkan)', 'rumus' => null, 'hasil' => $result];

            return $steps;
        };

        [$sumber, $field, $langkah, $rincian, $cek] = match ($no) {
            1, 2, 6, 9 => $this->explainMetaSum($no, $start, $end, $in),
            3 => $this->explainReach($start, $end, $in),
            15 => $this->explainChats($start, $end, $in),
            22 => [
                'API pusat GET /warehouse-summary?start_date=' . $start . '&end_date=' . $end,
                'data.summary.spk_periode_non_gr − spk_rd_periode',
                [
                    ['op' => '−', 'teks' => 'Semua SPK terbit − SPK garansi -GR', 'rumus' => $show('spk') . ' − ' . $show('spk_gr'), 'hasil' => $show('spk_non_gr')],
                    ['op' => '−', 'teks' => 'Dikurangi SPK R&D (nomor RD-)', 'rumus' => $show('spk_non_gr') . ' − ' . $show('spk_rd'), 'hasil' => $show('spk_closing')],
                    ['op' => '=', 'teks' => 'Nilai di kartu (sama dengan SPK Closing di Dashboard)', 'rumus' => null, 'hasil' => $result],
                ],
                [],
                isset($in['spk'], $in['spk_gr'], $in['spk_non_gr']) ? [
                    ['ok' => $in['spk'] - $in['spk_gr'] == $in['spk_non_gr'], 'teks' => 'total_incoming − spk_gr_periode = spk_periode_non_gr (' . $n($in['spk_non_gr']) . ')'],
                    ['ok' => isset($in['spk_rd']), 'teks' => isset($in['spk_rd']) ? 'SPK R&D yang tidak dihitung: ' . $n($in['spk_rd']) : 'Field spk_rd_periode tidak ada di respons; R&D ikut terhitung'],
                ] : [],
            ],
            23 => [
                'API pusat GET /warehouse-summary?start_date=' . $start . '&end_date=' . $end,
                'data.summary.spk_belum_tiba_periode',
                [
                    ['op' => '−', 'teks' => 'SPK terbit tanpa -GR − Sepatu tiba (sudah QC awal gudang)', 'rumus' => $show('spk_non_gr') . ' − ' . $show('arrived'), 'hasil' => isset($in['spk_non_gr'], $in['arrived']) ? $n($in['spk_non_gr'] - $in['arrived']) : '–'],
                    ['op' => '=', 'teks' => 'Nilai di kartu (dihitung di sistem pusat)', 'rumus' => null, 'hasil' => $result],
                ],
                [],
                isset($in['spk_non_gr'], $in['arrived'], $in['not_arrived']) ? [
                    ['ok' => $in['spk_non_gr'] - $in['arrived'] === $in['not_arrived'], 'teks' => 'SPK tanpa -GR − sepatu tiba = nilai dari sistem pusat (' . $n($in['not_arrived']) . ')'],
                    ['ok' => true, 'teks' => 'SPK garansi -GR yang tidak dihitung: ' . $n($in['spk_gr'] ?? 0) . ' (total SPK terbit ' . $show('spk') . ')'],
                ] : [],
            ],
            24 => ['API pusat warehouse-summary', 'spk_belum_tiba_periode ÷ spk_periode_non_gr', $ratio('not_arrived', 'spk_non_gr', 100), [], []],
            31 => ['Meta Insights + API pusat warehouse-summary', 'spend ÷ spk_tiba_periode', $ratio('spend', 'arrived'), [], []],
            12 => [
                $this->metaSource($start, $end),
                'action_values[omni_purchase], spend',
                $ratio('purchase_value', 'spend'),
                [],
                isset($in['meta_purchase_roas']) ? [['ok' => abs($in['meta_purchase_roas'] - ($value ?? 0)) < 0.001, 'teks' => 'Sama dengan purchase_roas versi Meta: ' . $n($in['meta_purchase_roas'], 4) . 'x']] : [],
            ],
            13, 14 => [
                'Meta Insights + API pusat GET /finance/dashboard?start_date=' . $start . '&end_date=' . $end,
                'metrics.omset_tagihan ÷ spend',
                $ratio('revenue', 'spend'),
                [],
                isset($in['shipping']) ? [['ok' => true, 'teks' => 'Ongkir ' . $rp($in['shipping']) . ' tidak termasuk omset (dicatat terpisah di sistem pusat)']] : [],
            ],
            17, 18 => $this->explainResponse($no, $start, $end),
            19 => [
                'API pusat dashboard-summary + tabel sleekflow_daily_analytics',
                '(number_of_new_enquires − total_leads) ÷ number_of_new_enquires',
                array_merge(
                    [['op' => '−', 'teks' => 'Total chat masuk baru − Jumlah isi data diri', 'rumus' => $show('chats') . ' − ' . $show('leads'), 'hasil' => $show('not_leads')]],
                    $ratio('not_leads', 'chats', 100),
                ),
                [],
                [],
            ],
            20 => [
                'API pusat GET /dashboard-summary?start_date=' . $start . '&end_date=' . $end,
                'data.summary.total_leads',
                [['op' => '=', 'teks' => 'Diambil langsung dari sistem pusat: cs_leads dibuat di periode, semua status, tanpa terhapus dan tanpa import greeting', 'rumus' => null, 'hasil' => $result]],
                [],
                isset($in['leads_converted']) ? [['ok' => $in['leads_converted'] <= $in['leads'], 'teks' => 'Pembanding: yang sudah CONVERTED ' . $n($in['leads_converted']) . ' (angka yang dipakai laporan Tim Data)']] : [],
            ],
            21 => ['API pusat dashboard-summary + tabel sleekflow_daily_analytics', 'total_leads ÷ number_of_new_enquires', $ratio('leads', 'chats', 100), [], []],
            25 => [
                'API pusat GET /finance/dashboard?start_date=' . $start . '&end_date=' . $end,
                'data.metrics.jumlah_tagihan',
                [['op' => '=', 'teks' => 'Diambil langsung dari sistem pusat: invoice dibuat di periode, bukan Batal, nilai jasa > 0', 'rumus' => null, 'hasil' => $result]],
                [],
                [],
            ],
            26 => ['API pusat finance/dashboard', 'jumlah_spk_tagihan ÷ jumlah_tagihan', $ratio('invoice_spk', 'invoices'), [], []],
            28 => ['API pusat finance/dashboard', 'omset_tagihan ÷ jumlah_spk_tagihan', $ratio('revenue', 'invoice_spk'), [], []],
            29 => ['API pusat finance/dashboard', 'omset_tagihan ÷ jumlah_tagihan', $ratio('revenue', 'invoices'), [], []],
            4 => [$this->metaSource($start, $end), 'impressions, reach', $ratio('impressions', 'reach'), [], []],
            5 => [$this->metaSource($start, $end), 'spend, impressions', $ratio('spend', 'impressions', 1000), [], []],
            7 => [$this->metaSource($start, $end), 'inline_link_clicks, impressions', $ratio('link_clicks', 'impressions', 100), [], []],
            8 => [$this->metaSource($start, $end), 'spend, inline_link_clicks', $ratio('spend', 'link_clicks'), [], []],
            10 => [$this->metaSource($start, $end), 'spend, actions[' . self::CHAT_ACTION . ']', $ratio('spend', 'ad_chats'), [], []],
            11 => [$this->metaSource($start, $end), 'actions[' . self::CHAT_ACTION . '], inline_link_clicks', $ratio('ad_chats', 'link_clicks', 100), [], []],
            16 => ['Meta Insights + tabel sleekflow_daily_analytics', 'ad_chats (Meta) ÷ number_of_new_enquires (Sleekflow)', $ratio('ad_chats', 'chats', 100), [], []],
            27 => ['API pusat warehouse-summary + tabel sleekflow_daily_analytics', '(spk_periode_non_gr − spk_rd_periode) ÷ number_of_new_enquires', $ratio('spk_closing', 'chats', 100), [], []],
            30 => ['Meta Insights + API pusat warehouse-summary', 'spend ÷ (spk_periode_non_gr − spk_rd_periode)', $ratio('spend', 'spk_closing'), [], []],
        };

        [$prevStart, $prevEnd] = $this->previousRange($start, $end);
        $prevData = $this->collect($prevStart, $prevEnd);
        $mom = $this->compare($no, $value, $prevData['values'][$no], $data['days'], $prevData['days']);
        $cek[] = [
            'ok' => $mom['status'] !== 'merah',
            'teks' => 'Bulan lalu (' . $prevStart . ' s/d ' . $prevEnd . '): ' . $this->format($no, $prevData['values'][$no])
                . ($mom['perubahan'] === null ? '' : '; perubahan ' . ($mom['perubahan'] >= 0 ? '+' : '') . $n($mom['perubahan'], 1) . '%'
                    . (in_array($no, config('management_metrics.mom.jumlah'), true) ? ' (per hari)' : ''))
                . ($mom['status'] ? '; lampu ' . $mom['status'] : '; tanpa lampu MoM, memakai target Kamus bila ada'),
        ];

        if (($target = $def['target'] ?? null) && $value !== null) {
            $met = $this->meetsTarget($no, $value, $data['days']);
            $compared = empty($target['per_hari'])
                ? $result
                : $result . ' ÷ ' . $data['days'] . ' hari = ' . $n($value / $data['days'], in_array($no, [1], true) ? 0 : 1) . '/hari';
            $cek[] = [
                'ok' => (bool) $met,
                'teks' => 'Target sehat ' . $target['teks'] . ': ' . $compared . ($met ? ' (sesuai target)' : ' (di luar target)'),
            ];
        }

        if ($warning = $def['peringatan'] ?? null) {
            $cek[] = ['ok' => false, 'teks' => $warning];
        }

        foreach ($data['errors'] as $error) {
            $cek[] = ['ok' => false, 'teks' => $error];
        }

        return [
            'judul' => "No. {$no} · {$def['nama']}",
            'sifat' => 'periode',
            'endpoint' => $sumber,
            'field' => $field,
            'nilai' => $result,
            'definisi' => $def['definisi'],
            'rumus_db' => $def['rumus'],
            'hitung' => ['langkah' => $langkah, 'rincian' => $rincian],
            'cek' => $cek,
            'label_nilai' => 'Nilai di Kartu',
            'label_rumus' => 'Rumus',
            'sumber_rumus' => 'Acuan: Presentasi Data Ads & CS Manajemen SW dan Kamus Metrik v2.0 (Tim Data). Periode ' . $start . ' s/d ' . $end . '.',
        ];
    }

    /** Hapus cache sumber API untuk rentang ini (tombol Perbarui Data). */
    public function forget(string $start, string $end): void
    {
        foreach (['totals', 'daily'] as $kind) {
            Cache::forget("mgmt_meta_{$kind}_{$start}_{$end}");
        }
        foreach (['gudang', 'leads', 'inv', 'inv_daily', 'cs'] as $kind) {
            Cache::forget("mgmt_{$kind}_{$start}_{$end}");
        }
    }

    // ------------------------------------------------------------------ sumber data

    /**
     * Total akun Meta untuk seluruh rentang dalam satu panggilan (jangkauan harus diambil begini, tidak bisa dijumlah).
     */
    protected function metaTotals(string $start, string $end): array
    {
        return Cache::remember("mgmt_meta_totals_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            $row = $this->metaInsights($start, $end, ['spend', 'impressions', 'reach', 'inline_link_clicks', 'actions', 'action_values', 'purchase_roas'])[0] ?? [];
            $purchase = fn (string $field) => collect($row[$field] ?? [])->firstWhere('action_type', 'omni_purchase')['value'] ?? null;

            return $this->metaRow($row) + array_filter([
                'reach' => (int) ($row['reach'] ?? 0),
                'purchase_value' => (float) ($purchase('action_values') ?? 0),
                'meta_purchase_roas' => ($v = $purchase('purchase_roas')) === null ? null : (float) $v,
            ], fn ($v) => $v !== null);
        });
    }

    /** Nilai Meta per hari, hanya untuk rincian di modal. */
    protected function metaDaily(string $start, string $end): array
    {
        return Cache::remember("mgmt_meta_daily_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            $rows = $this->metaInsights($start, $end, ['spend', 'impressions', 'reach', 'inline_link_clicks', 'actions'], daily: true);

            return collect($rows)
                ->mapWithKeys(fn ($row) => [$row['date_start'] => $this->metaRow($row) + ['reach' => (int) ($row['reach'] ?? 0)]])
                ->sortKeys()
                ->all();
        });
    }

    protected function metaInsights(string $start, string $end, array $fields, bool $daily = false): array
    {
        $token = config('services.meta.access_token') ?? env('META_ADS_ACCESS_TOKEN');
        if (! $token) {
            throw new \RuntimeException('token Meta belum diatur.');
        }

        $params = array_filter([
            'access_token' => $token,
            'level' => 'account',
            'fields' => implode(',', $fields),
            'time_range' => json_encode(['since' => $start, 'until' => $end]),
            'time_increment' => $daily ? 1 : null,
            'limit' => 100,
        ]);

        $url = self::GRAPH_URL . '/' . config('services.meta.ad_account_id') . '/insights';
        $rows = [];

        // Paginasi Graph API (data harian bisa lebih dari satu halaman)
        while ($url) {
            $response = Http::timeout(60)->get($url, $params);

            if ($response->failed()) {
                Log::error('ManagementMetrics: Meta Insights gagal', ['body' => $response->body()]);
                throw new \RuntimeException($response->json('error.message') ?? 'HTTP ' . $response->status());
            }

            $rows = array_merge($rows, $response->json('data') ?? []);
            $url = $response->json('paging.next');
            $params = [];
        }

        return $rows;
    }

    protected function metaRow(array $row): array
    {
        $chats = collect($row['actions'] ?? [])->firstWhere('action_type', self::CHAT_ACTION);

        return [
            'spend' => (float) ($row['spend'] ?? 0),
            'impressions' => (int) ($row['impressions'] ?? 0),
            'link_clicks' => (int) ($row['inline_link_clicks'] ?? 0),
            'ad_chats' => (int) ($chats['value'] ?? 0),
        ];
    }

    /** SPK terbit dan sepatu tiba / belum tiba (rumus Kamus, tanpa SPK garansi -GR) dari API pusat. */
    protected function warehouseCounts(string $start, string $end): array
    {
        return Cache::remember("mgmt_gudang_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            $summary = data_get(app(WarehouseApiService::class)->fetchSummary($start, $end), 'summary', []);

            // spk_rd_periode (SPK R&D, nomor RD-) opsional: kartu Gudang menampilkan "menyusul" sampai ERP mengirimnya
            return $this->pick($summary, 'summary', [
                'spk' => 'total_incoming',
                'spk_non_gr' => 'spk_periode_non_gr',
                'spk_gr' => 'spk_gr_periode',
                'arrived' => 'spk_tiba_periode',
                'not_arrived' => 'spk_belum_tiba_periode',
            ]) + (isset($summary['spk_rd_periode']) ? ['spk_rd' => (float) $summary['spk_rd_periode']] : [])
                + $this->optional($summary, [
                    // Kamus No. 32 Intake Fisik: sepatu yang tiba (QC gudang) pada periode, menurut tanggal tiba
                    'fisik_total' => 'total_qc_processed',
                    // Rincian per jenis (field baru ERP, opsional)
                    'fisik_berbayar' => 'intake_fisik_berbayar',
                    'fisik_garansi' => 'intake_fisik_garansi',
                    'fisik_rd' => 'intake_fisik_rd',
                ]);
        });
    }

    /** Data diri customer (cs_leads) dari dashboard-summary. */
    protected function leadCounts(string $start, string $end): array
    {
        return Cache::remember("mgmt_leads_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            return $this->pick($this->pusat('dashboard-summary', $start, $end, 'summary'), 'summary', [
                'leads' => 'total_leads',
                'leads_converted' => 'total_leads_converted',
            ]);
        });
    }

    /** Tagihan valid dari finance/dashboard: jumlah, omset jasa bersih (tanpa ongkir), SPK yang menempel. */
    protected function invoiceCounts(string $start, string $end): array
    {
        return Cache::remember("mgmt_inv_{$start}_{$end}", now()->addMinutes(self::CACHE_MINUTES), function () use ($start, $end) {
            return $this->pick($this->pusat('finance/dashboard', $start, $end, 'metrics'), 'metrics', [
                'invoices' => 'jumlah_tagihan',
                'revenue' => 'omset_tagihan',
                'invoice_spk' => 'jumlah_spk_tagihan',
                'shipping' => 'ongkir_tagihan',
            ]);
        });
    }

    /** GET ke API pusat dengan header X-API-KEY, mengembalikan data.{$section}. */
    protected function pusat(string $endpoint, string $start, string $end, string $section): array
    {
        $response = Http::timeout(60)
            ->withHeaders(['X-API-KEY' => config('services.dashboard.key'), 'Accept' => 'application/json'])
            ->get(rtrim(config('services.dashboard.base_url'), '/') . '/' . $endpoint, ['start_date' => $start, 'end_date' => $end]);

        if ($response->failed()) {
            throw new \RuntimeException("{$endpoint} HTTP " . $response->status());
        }

        return $response->json("data.{$section}") ?? [];
    }

    /** Ambil field opsional dari respons API pusat; yang tidak ada dilewati. */
    protected function optional(array $node, array $map): array
    {
        $out = [];
        foreach ($map as $key => $field) {
            if (isset($node[$field]) && is_numeric($node[$field])) {
                $out[$key] = (float) $node[$field];
            }
        }

        return $out;
    }

    /** Ambil field wajib dari respons API pusat; field yang hilang = ERP belum di-deploy. */
    protected function pick(array $node, string $section, array $map): array
    {
        $out = [];
        foreach ($map as $key => $field) {
            if (! isset($node[$field])) {
                throw new \RuntimeException("field {$section}.{$field} tidak ada di respons.");
            }
            $out[$key] = (float) $node[$field];
        }

        return $out;
    }

    /**
     * Rata-rata waktu balas harian Sleekflow (detik), dengan aturan yang sama dengan kartu di halaman Analitik
     * Sleekflow (App\Livewire\Sleekflow\Analytics::countedResponseDay): hari libur CS tidak dihitung.
     */
    protected function responseTimes(string $start, string $end): array
    {
        $row = $this->sleekflowQuery($start, $end)->selectRaw("
            AVG(CASE WHEN {$this->countedDay('response_time_first_messages')} THEN TIME_TO_SEC(response_time_first_messages) END) as resp_first,
            AVG(CASE WHEN {$this->countedDay('response_time_all_messages')} THEN TIME_TO_SEC(response_time_all_messages) END) as resp_all
        ")->first();

        return array_filter([
            'resp_first' => $row?->resp_first === null ? null : (float) $row->resp_first,
            'resp_all' => $row?->resp_all === null ? null : (float) $row->resp_all,
        ], fn ($v) => $v !== null);
    }

    protected function countedDay(string $column): string
    {
        return "(response_time_first_messages <> '00:00:00' AND {$column} <> '00:00:00')";
    }

    protected function sleekflowQuery(string $start, string $end)
    {
        return SleekflowDailyAnalytics::query()
            ->whereDate('date_time', '>=', $start)
            ->whereDate('date_time', '<=', $end);
    }

    // ------------------------------------------------------------------ rincian modal

    protected function metaSource(string $start, string $end): string
    {
        return 'Meta Graph API GET /' . config('services.meta.ad_account_id') . "/insights?level=account&time_range={$start}..{$end}";
    }

    /** Data Meta yang bisa dijumlah per hari: total dari Meta, dibuktikan dengan jumlah nilai hariannya. */
    protected function explainMetaSum(int $no, string $start, string $end, array $in): array
    {
        [$key, $field] = match ($no) {
            1 => ['spend', 'spend'],
            2 => ['impressions', 'impressions'],
            6 => ['link_clicks', 'inline_link_clicks'],
            9 => ['ad_chats', 'actions[' . self::CHAT_ACTION . ']'],
        };

        $fmt = fn ($v) => $key === 'spend' ? 'Rp ' . number_format($v, 0, ',', '.') : number_format($v, 0, ',', '.');
        $daily = $this->safeDaily($start, $end);
        $values = array_map(fn ($d) => $d[$key], $daily);
        $sum = array_sum($values);

        $langkah = [
            ['op' => '+', 'teks' => 'Jumlahkan nilai harian dari Meta (' . count($values) . ' hari ada data)', 'rumus' => $this->series(array_map($fmt, $values)), 'hasil' => $fmt($sum)],
            ['op' => '=', 'teks' => 'Nilai di kartu: total akun dari Meta untuk seluruh rentang', 'rumus' => null, 'hasil' => isset($in[$key]) ? $fmt($in[$key]) : '–'],
        ];

        $cek = isset($in[$key]) ? [[
            'ok' => abs($sum - $in[$key]) < 1,
            'teks' => 'Jumlah nilai harian ' . $fmt($sum) . ' = total Meta ' . $fmt($in[$key]),
        ]] : [];

        return [$this->metaSource($start, $end), $field, $langkah, $this->dailyRows($daily, $key, $fmt), $cek];
    }

    /** Jangkauan tidak boleh dijumlah: tunjukkan bedanya dengan jumlah harian. */
    protected function explainReach(string $start, string $end, array $in): array
    {
        $fmt = fn ($v) => number_format($v, 0, ',', '.');
        $daily = $this->safeDaily($start, $end);
        $sum = array_sum(array_map(fn ($d) => $d['reach'], $daily));

        $langkah = [
            ['op' => '=', 'teks' => 'Diambil langsung dari Meta untuk seluruh rentang (orang unik, tidak dijumlah per hari)', 'rumus' => null, 'hasil' => isset($in['reach']) ? $fmt($in['reach']) : '–'],
        ];

        $cek = [[
            'ok' => true,
            'teks' => 'Kalau jangkauan harian dijumlah hasilnya ' . $fmt($sum) . ', lebih besar karena orang yang sama terhitung lagi tiap hari. Angka ini tidak dipakai.',
        ]];

        return [$this->metaSource($start, $end), 'reach', $langkah, $this->dailyRows($daily, 'reach', $fmt), $cek];
    }

    protected function explainChats(string $start, string $end, array $in): array
    {
        $rows = $this->sleekflowQuery($start, $end)->orderBy('date_time')->get(['date_time', 'number_of_new_enquires']);
        $fmt = fn ($v) => number_format($v, 0, ',', '.');
        $values = $rows->pluck('number_of_new_enquires')->map(fn ($v) => (int) $v)->all();
        $days = $this->days($start, $end);

        $langkah = [
            ['op' => '+', 'teks' => 'Jumlahkan prospek baru per hari (' . count($values) . ' dari ' . $days . ' hari ada data)', 'rumus' => $this->series(array_map($fmt, $values)), 'hasil' => $fmt(array_sum($values))],
            ['op' => '=', 'teks' => 'Nilai di kartu', 'rumus' => null, 'hasil' => $fmt($in['chats'] ?? 0)],
        ];

        $rincian = $rows->map(fn ($r) => [
            'tanggal' => Carbon::parse($r->date_time)->translatedFormat('D, j M Y'),
            'nilai' => $fmt($r->number_of_new_enquires),
            'keluar' => false,
            'ket' => null,
        ])->all();

        $cek = [['ok' => count($values) === $days, 'teks' => 'Hari yang punya data Sleekflow: ' . count($values) . ' dari ' . $days . ' hari']];

        return ['Tabel sleekflow_daily_analytics (sinkron dari Sleekflow GET /analytic)', 'number_of_new_enquires', $langkah, $rincian, $cek];
    }

    protected function explainResponse(int $no, string $start, string $end): array
    {
        $column = $no === 17 ? 'response_time_first_messages' : 'response_time_all_messages';
        $rows = $this->sleekflowQuery($start, $end)->orderBy('date_time')->get(['date_time', 'response_time_first_messages', 'response_time_all_messages']);
        $counted = $rows->filter(fn ($r) => $r->response_time_first_messages && $r->response_time_first_messages !== '00:00:00'
            && $r->{$column} && $r->{$column} !== '00:00:00');
        // Durasi TIME bisa lebih dari 24 jam, jadi diurai manual (bukan sebagai jam di hari itu)
        $toSeconds = fn (?string $t) => array_sum(array_map(fn ($p, $m) => (int) $p * $m, explode(':', $t ?: '0:0:0') + [0, 0, 0], [3600, 60, 1]));
        $seconds = $counted->map(fn ($r) => $toSeconds($r->{$column}))->values()->all();
        $sum = array_sum($seconds);
        $n = fn ($v) => number_format($v, 0, ',', '.');

        $langkah = [
            ['op' => '−', 'teks' => 'Hari yang punya data − hari libur CS (tanpa balasan staf)', 'rumus' => $rows->count() . ' − ' . ($rows->count() - $counted->count()), 'hasil' => $counted->count() . ' hari dihitung'],
            ['op' => '+', 'teks' => 'Jumlahkan waktu balas harian (detik)', 'rumus' => $this->series(array_map($n, $seconds)), 'hasil' => $n($sum) . ' detik'],
            ['op' => '÷', 'teks' => 'Bagi jumlah hari yang dihitung', 'rumus' => $n($sum) . ' ÷ ' . $counted->count(), 'hasil' => $counted->count() ? $n($sum / $counted->count()) . ' detik' : '–'],
            ['op' => '=', 'teks' => 'Nilai di kartu (jj:mm:dd, dibulatkan ke bawah)', 'rumus' => null, 'hasil' => $counted->count() ? $this->duration($sum / $counted->count()) : '–'],
        ];

        $rincian = $rows->map(fn ($r, $i) => [
            'tanggal' => Carbon::parse($r->date_time)->translatedFormat('D, j M Y'),
            'nilai' => $r->{$column} ?: '00:00:00',
            'keluar' => ! $counted->has($i),
            'ket' => $counted->has($i) ? null : 'Libur CS, tidak dihitung',
        ])->values()->all();

        return ['Tabel sleekflow_daily_analytics (sinkron dari Sleekflow GET /analytic)', $column, $langkah, $rincian, []];
    }

    protected function safeDaily(string $start, string $end): array
    {
        try {
            return $this->metaDaily($start, $end);
        } catch (Throwable) {
            return [];
        }
    }

    protected function dailyRows(array $daily, string $key, callable $fmt): array
    {
        return collect($daily)->map(fn ($d, $date) => [
            'tanggal' => Carbon::parse($date)->translatedFormat('D, j M Y'),
            'nilai' => $fmt($d[$key]),
            'keluar' => false,
            'ket' => null,
        ])->values()->all();
    }

    /** Deret "a + b + …" untuk langkah penjumlahan; dipotong di tengah jika panjang. */
    protected function series(array $terms): string
    {
        $count = count($terms);

        return $count > 12
            ? implode(' + ', array_slice($terms, 0, 5)) . ' + … + ' . implode(' + ', array_slice($terms, -3)) . " ({$count} angka)"
            : (implode(' + ', $terms) ?: '0');
    }
}
