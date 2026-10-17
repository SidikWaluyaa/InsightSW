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
