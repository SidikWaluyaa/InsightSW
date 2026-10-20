<section class="space-y-4">
    {{-- Modal rumus (tombol "i", hanya Admin) --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
        <div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Marketing & Iklan Meta Ads (CTWA)</h3>
            <p class="text-xs text-slate-500 dark:text-gray-400">Efisiensi biaya iklan dan rincian per materi iklan & kampanye. Biaya Meta tanpa pajak.</p>
        </div>
        <a href="{{ route('meta-ads', ['startDate' => $start, 'endDate' => $end]) }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline shrink-0">Buka halaman Meta Ads →</a>
    </div>

    <x-meta-ads.kpi-cards :kpi="$kpi" :info="$showFormula" />

    <div>
        <p class="text-[11px] text-slate-500 dark:text-gray-400 mb-2">Tabel berjenjang: tanggal hanya ditulis di baris pertama tanggal itu, materi iklan hanya di baris pertama materi itu; nama kampanye dan angka selalu terisi.</p>
        <x-meta-ads.hierarchy-table :hierarchy="$hierarchy" />
    </div>
</section>
