<?php

namespace App\Services;

use App\Models\MetaAdsReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Ringkasan iklan Meta dari tabel meta_ads_reports (PRD halaman 4): 4 KPI efisiensi dengan lampu MoM dan tabel
 * berjenjang Tanggal → Materi iklan → Kampanye. Dipakai halaman Meta Ads dan Dashboard Marketing supaya angkanya sama.
 * Biaya = spend Meta tanpa pajak.
 */
class MetaAdsReportService
{
    public function __construct(protected ManagementMetricsService $metrics)
    {
    }

    /**
     * @param  array{campaign?: string, adset?: string, search?: string}  $filters
     */
    public function query(string $start, string $end, array $filters = []): Builder
    {
        $search = $filters['search'] ?? '';

        return MetaAdsReport::query()
            ->whereBetween('date', [$start, $end])
            ->when($filters['campaign'] ?? '', fn ($q, $v) => $q->where('campaign_name', $v))
            ->when($filters['adset'] ?? '', fn ($q, $v) => $q->where('adset_name', $v))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('ad_name', 'like', "%{$search}%")
                        ->orWhere('campaign_name', 'like', "%{$search}%")
                        ->orWhere('adset_name', 'like', "%{$search}%");
                });
            });
    }

    /**
     * 4 KPI efisiensi (biaya, chat, biaya per chat, CTR klik link) dibanding periode yang sama bulan lalu.
     *
     * @return array{cards: array, days: int, prevRange: array}
     */
    public function kpis(string $start, string $end, array $filters = []): array
    {
        $totals = function (string $s, string $e) use ($filters) {
            $row = $this->query($s, $e, $filters)
                ->selectRaw('SUM(spend) spend, SUM(results) results, SUM(link_click) link_click, SUM(impressions) impressions')
                ->first();
            $spend = (float) $row->spend;
            $results = (float) $row->results;
            $impressions = (float) $row->impressions;

            return [
                'spend' => $spend,
                'results' => $results,
                'cpr' => $results > 0 ? $spend / $results : null,
                'ctr' => $impressions > 0 ? $row->link_click / $impressions * 100 : null,
            ];
        };

        [$prevStart, $prevEnd] = $this->metrics->previousRange($start, $end);
        $now = $totals($start, $end);
        $prev = $totals($prevStart, $prevEnd);
        $days = $this->metrics->days($start, $end);
        $prevDays = $this->metrics->days($prevStart, $prevEnd);

        // [arah, dibanding per hari]: arah null = tanpa lampu (biaya iklan bukan baik/buruk)
        $rules = ['spend' => [null, true], 'results' => ['naik', true], 'cpr' => ['turun', false], 'ctr' => ['naik', false]];
        $cards = [];
        foreach ($rules as $key => [$arah, $perDay]) {
            $cards[$key] = ['nilai' => $now[$key], 'lalu' => $prev[$key]]
                + $this->metrics->compareValues($now[$key], $prev[$key] ?: null, $arah, $perDay, $days, $prevDays);
        }

        return ['cards' => $cards, 'days' => $days, 'prevRange' => [$prevStart, $prevEnd]];
    }

    /**
     * Isi modal info rumus (tombol "i", Admin) untuk 4 KPI: sumber, rumus, langkah hitung dengan angka periode,
     * nilai per hari, perbandingan bulan lalu, dan cek silang dengan total akun dari Meta Graph API.
     */
    public function explain(string $key, string $start, string $end, array $filters = []): ?array
    {
        $defs = [
            'spend' => ['Ad Spend (Total)', 'SUM(spend)', 'Total biaya iklan Meta pada periode, tanpa pajak (PPN). Budget harian = total ÷ jumlah hari kalender rentang.'],
            'results' => ['Chat Results (CTWA)', 'SUM(results)', 'Jumlah hasil iklan Click-to-WhatsApp: percakapan WhatsApp yang dimulai dari iklan.'],
            'cpr' => ['Cost per Result', 'SUM(spend) ÷ SUM(results)', 'Rata-rata biaya iklan untuk mendapat satu chat dari iklan. Turun = lebih baik.'],
            'ctr' => ['Click-Through Rate (CTR)', 'SUM(link_click) ÷ SUM(impressions) × 100', 'Persen tayangan iklan yang diklik link-nya (klik link, bukan semua klik).'],
        ];

        if (! isset($defs[$key])) {
            return null;
        }

        [$judul, $rumus, $definisi] = $defs[$key];
        $n = fn ($v, $dec = 0) => $v === null ? '–' : number_format((float) $v, $dec, ',', '.');
        $rp = fn ($v) => $v === null ? '–' : 'Rp ' . $n($v);
        $fmt = fn ($v) => match ($key) {
            'spend', 'cpr' => $rp($v),
            'ctr' => $v === null ? '–' : $n($v, 2) . '%',
            default => $n($v),
        };

        $sum = $this->query($start, $end, $filters)
            ->selectRaw('SUM(spend) spend, SUM(results) results, SUM(link_click) link_click, SUM(impressions) impressions')
            ->first();
        $kpi = $this->kpis($start, $end, $filters);
        $card = $kpi['cards'][$key];
        $days = $kpi['days'];

        $langkah = match ($key) {
            'spend' => [
                ['op' => '+', 'teks' => 'Jumlahkan biaya semua iklan pada semua hari', 'rumus' => 'SUM(spend) dari meta_ads_reports', 'hasil' => $rp($sum->spend)],
                ['op' => '÷', 'teks' => 'Budget harian = total ÷ jumlah hari', 'rumus' => $rp($sum->spend) . ' ÷ ' . $days . ' hari', 'hasil' => $rp($sum->spend / max($days, 1))],
            ],
            'results' => [
                ['op' => '+', 'teks' => 'Jumlahkan hasil (chat) semua iklan pada semua hari', 'rumus' => 'SUM(results) dari meta_ads_reports', 'hasil' => $n($sum->results)],
            ],
            'cpr' => [
                ['op' => '÷', 'teks' => 'Ad spend ÷ chat results', 'rumus' => $rp($sum->spend) . ' ÷ ' . $n($sum->results), 'hasil' => $rp($card['nilai'])],
            ],
            'ctr' => [
                ['op' => '÷', 'teks' => 'Klik link ÷ tayangan', 'rumus' => $n($sum->link_click) . ' ÷ ' . $n($sum->impressions), 'hasil' => $sum->impressions > 0 ? $n($sum->link_click / $sum->impressions, 6) : '–'],
                ['op' => '×', 'teks' => 'Kali 100 supaya jadi persen', 'rumus' => null, 'hasil' => $fmt($card['nilai'])],
            ],
        };
        $langkah[] = ['op' => '=', 'teks' => 'Nilai di kartu', 'rumus' => null, 'hasil' => $fmt($card['nilai'])];

        // Nilai per hari untuk rincian
        $daily = $this->query($start, $end, $filters)
            ->selectRaw('date, SUM(spend) spend, SUM(results) results, SUM(link_click) link_click, SUM(impressions) impressions')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn ($d) => [
                'tanggal' => \Carbon\Carbon::parse($d->date)->translatedFormat('d M Y'),
                'nilai' => $fmt(match ($key) {
                    'spend' => (float) $d->spend,
                    'results' => (float) $d->results,
                    'cpr' => $d->results > 0 ? $d->spend / $d->results : null,
                    'ctr' => $d->impressions > 0 ? $d->link_click / $d->impressions * 100 : null,
                }),
                'ket' => null,
            ])->all();

        [$ps, $pe] = $kpi['prevRange'];
        $cek = [[
            'ok' => $card['status'] !== 'merah',
            'teks' => "Bulan lalu ({$ps} s/d {$pe}): " . $fmt($card['lalu'])
                . ($card['perubahan'] === null ? '' : '; perubahan ' . ($card['perubahan'] >= 0 ? '+' : '') . $n($card['perubahan'], 1) . '%' . (in_array($key, ['spend', 'results'], true) ? ' (per hari)' : ''))
                . ($card['status'] ? '; lampu ' . $card['status'] : ($key === 'spend' ? '; tanpa lampu (biaya bukan baik/buruk)' : '')),
        ]];

        // Cek silang: total tabel lokal vs total akun langsung dari Meta Graph API (hanya tanpa filter)
        if (! array_filter($filters)) {
            $meta = $this->metrics->collect($start, $end)['inputs'];
            $map = ['spend' => ['spend', $sum->spend], 'results' => ['ad_chats', $sum->results], 'ctr' => ['link_clicks', $sum->link_click]];
            if (isset($map[$key], $meta[$map[$key][0]])) {
                [$field, $local] = $map[$key];
                $selisih = abs((float) $local - (float) $meta[$field]);
                $cek[] = [
                    'ok' => $selisih <= max(1, (float) $meta[$field] * 0.005),
                    'teks' => 'Meta Graph API (total akun) ' . $field . ' = ' . ($field === 'spend' ? $rp($meta[$field]) : $n($meta[$field]))
                        . ' ; tabel lokal = ' . ($field === 'spend' ? $rp($local) : $n($local)) . '. Beda kecil wajar (atribusi Meta berubah 1–3 hari).',
                ];
            }
        }

        return [
            'judul' => $judul,
            'sifat' => 'periode',
            'endpoint' => 'Tabel meta_ads_reports (sinkron Meta Insights level iklan per hari, terjadwal tiap jam)',
            'field' => $rumus,
            'nilai' => $fmt($card['nilai']),
            'definisi' => $definisi,
            'rumus_db' => 'SELECT ' . ['spend' => 'SUM(spend)', 'results' => 'SUM(results)', 'cpr' => 'SUM(spend) / NULLIF(SUM(results), 0)', 'ctr' => 'SUM(link_click) / NULLIF(SUM(impressions), 0) * 100'][$key]
                . "\nFROM meta_ads_reports\nWHERE date BETWEEN :start AND :end"
                . (array_filter($filters) ? "\n  -- ditambah filter kampanye / adset / pencarian yang aktif" : ''),
            'hitung' => ['langkah' => $langkah, 'rincian' => $daily],
            'cek' => $cek,
            'label_nilai' => 'Nilai di Kartu',
            'label_rumus' => 'Rumus',
            'sumber_rumus' => "Acuan: PRD Master Blueprint v3.0 halaman 4 dan Kamus Metrik v2.0. Periode {$start} s/d {$end}.",
        ];
    }

    /**
     * Tabel berjenjang: Tanggal → Materi iklan → Kampanye, dipaginasi per tanggal (satu tanggal tidak terpotong).
     * Iklan yang tidak tayang dan tidak menghasilkan chat di hari itu tidak ditampilkan.
     */
    public function hierarchy(string $start, string $end, int $page, int $perPage = 7, array $filters = [], string $pageName = 'page'): LengthAwarePaginator
    {
        $dates = $this->query($start, $end, $filters)->distinct()->orderByDesc('date')->pluck('date');
        $pageDates = $dates->forPage($page, $perPage)->values();

        $rows = $pageDates->isEmpty() ? collect() : $this->query($start, $end, $filters)
            ->whereIn('date', $pageDates)
            ->selectRaw('date, ad_name, campaign_name, SUM(spend) spend, SUM(impressions) impressions, SUM(link_click) link_click, SUM(results) results')
            ->groupBy('date', 'ad_name', 'campaign_name')
            ->havingRaw('SUM(spend) > 0 OR SUM(results) > 0')
            ->get()
            // Urutan: tanggal terbaru, lalu materi dengan biaya terbesar, lalu kampanye dengan biaya terbesar
            ->groupBy(fn ($r) => Carbon::parse($r->date)->toDateString())
            ->sortKeysDesc()
            ->map(fn ($day) => $day->groupBy('ad_name')
                ->sortByDesc(fn ($ads) => $ads->sum('spend'))
                ->map(fn ($ads) => $ads->sortByDesc('spend')->values()));

        return new LengthAwarePaginator($rows, $dates->count(), $perPage, $page, ['path' => request()->url(), 'pageName' => $pageName]);
    }
}
