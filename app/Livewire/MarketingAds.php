<?php

namespace App\Livewire;

use App\Services\MetaAdsReportService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Bagian "Marketing & Iklan Meta Ads (CTWA)" di Dashboard Marketing (PRD halaman 4): 4 KPI efisiensi dan tabel
 * berjenjang Tanggal → Materi iklan → Kampanye. Sumber dan tampilan sama dengan halaman Meta Ads.
 */
#[Lazy]
class MarketingAds extends Component
{
    use WithPagination;

    /** Rentang dari filter Dashboard (Y-m-d). */
    #[Reactive]
    public string $start;

    #[Reactive]
    public string $end;

    protected const PAGE = 'hal_iklan';

    public function placeholder()
    {
        return <<<'HTML'
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-8 text-center text-sm text-slate-400 dark:text-gray-500 animate-pulse">
            Memuat data iklan Meta…
        </div>
        HTML;
    }

    /** Rentang berubah dari Dashboard: kembali ke halaman pertama tabel. */
    public function updated(): void
    {
        $this->resetPage(self::PAGE);
    }

    /** Tombol "i" (Admin): rincian rumus KPI iklan. */
    public function showFormula(string $key, MetaAdsReportService $report): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($info = $report->explain($key, $this->start, $this->end)) {
            $this->dispatch('show-formula', ...$info);
        }
    }

    public function render(MetaAdsReportService $report)
    {
        return view('livewire.marketing-ads', [
            'kpi' => $report->kpis($this->start, $this->end),
            'showFormula' => auth()->user()?->isAdmin() ?? false,
            'hierarchy' => $report->hierarchy($this->start, $this->end, $this->getPage(self::PAGE), 7, [], self::PAGE),
        ]);
    }
}
