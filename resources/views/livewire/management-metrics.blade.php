<div class="space-y-6">
    {{-- Modal rumus (dibuka tombol "i", hanya Admin); di luar blok yang di-render ulang agar Alpine tidak rusak --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.25em]">Standar Pantauan Manajemen</p>
            <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Data Iklan & CS</h2>
            <p class="text-xs text-slate-500 dark:text-gray-400 max-w-3xl leading-relaxed">
                Diurutkan mengikuti perjalanan customer: iklan dilihat, diklik, jadi chat, lalu jadi SPK. Nomor sesuai dokumen Tim Data.
                Lampu membandingkan dengan periode yang sama bulan lalu ({{ \Carbon\Carbon::parse($prevRange[0] ?? now())->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($prevRange[1] ?? now())->translatedFormat('j M Y') }}): hijau sama atau lebih baik, kuning lebih buruk sampai {{ config('management_metrics.mom.batas_kuning') }}%, merah lebih buruk dari itu.
                Data tanpa lampu bulan lalu memakai target Kamus Metrik v2.0. Kartu bercatatan merah belum layak jadi patokan.
                @if($trimmed)
                    <span class="font-semibold text-amber-600 dark:text-amber-400">Dihitung sampai {{ \Carbon\Carbon::parse($range[1])->translatedFormat('j M Y') }} (H-1), karena data Sleekflow hari ini belum tersedia.</span>
                @endif
            </p>
        </div>
        <x-ui.btn variant="secondary" icon="refresh" wire:click="refreshData" target="refreshData" class="shrink-0">Hitung Ulang</x-ui.btn>
    </div>

    @if(! $range)
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl text-sm text-slate-500 dark:text-gray-400">
            Belum ada data untuk rentang ini. Data dihitung sampai kemarin (H-1), jadi rentang yang hanya berisi hari ini baru tersedia besok.
        </div>
    @else
        @foreach($sourceErrors as $error)
            <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-sm text-rose-700 dark:text-rose-400">
                {{ $error }} Kartu yang memakai sumber ini ditampilkan "–".
            </div>
        @endforeach

        <div wire:loading.class="opacity-50" wire:target="refreshData" class="space-y-8 transition-opacity">
            @foreach(config('management_metrics.kelompok') as $groupKey => $group)
                <section class="space-y-4">
                    <x-ui.section-note :title="$group['judul']" :tag="$groupKey === 'iklan' ? 'Meta Ads' : 'Sleekflow + Sistem Pusat'" :tone="$groupKey === 'iklan' ? 'slate' : 'emerald'" />

                    @foreach($group['tahap'] as $stageKey => $stageLabel)
                        @continue(empty($groups[$groupKey][$stageKey]))
                        <div class="space-y-3">
                            <p class="text-[11px] font-bold text-slate-400 dark:text-gray-500">{{ $stageLabel }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                                @foreach($groups[$groupKey][$stageKey] as $no => $metric)
                                    <x-ui.stat-card wire:key="mgmt-{{ $no }}" class="!p-5" compact
                                        :label="$no . '. ' . $metric['nama']" :value="$metric['nilai']" :icon="$metric['icon']"
                                        :badge="$metric['badge']" :badge-tone="$metric['badgeTone']"
                                        :info-call="$showFormula ? 'showFormula(' . $no . ')' : null"
                                        :warning="$metric['peringatan'] ?? null"
                                        :note="$metric['singkat'] . ($metric['bulanLalu'] !== null ? ' Bulan lalu: ' . $metric['bulanLalu'] . '.' : '') . (isset($metric['target']) ? ' Target Kamus: ' . $metric['target']['teks'] . '.' : '')" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </section>
            @endforeach

            <p class="text-[11px] text-slate-400 dark:text-gray-500 leading-relaxed">
                Biaya iklan di bagian ini tanpa pajak, sesuai dokumen Tim Data. Kolom "Terpakai" di tabel harian sudah termasuk pajak.
                Data jumlah (biaya, tayang, chat, SPK, tagihan) dibandingkan per hari. Batas kuning {{ config('management_metrics.mom.batas_kuning') }}% belum angka resmi Tim Data.
                Target Kamus "/hari" dibandingkan dengan nilai dibagi jumlah hari kalender rentang.
                Angka dibaca per hari ini: tambah jasa, invoice batal, dan sepatu yang terlambat datang ikut mengubah angka periode lama.
            </p>
        </div>
    @endif
</div>
