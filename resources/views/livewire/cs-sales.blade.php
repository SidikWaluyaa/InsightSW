@php
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $rp = fn ($v) => $v === null ? '–' : 'Rp ' . number_format($v, 0, ',', '.');
@endphp

<div class="space-y-6">
    {{-- Modal rumus (tombol "i", hanya Admin) --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    <x-ui.page-header title="Penjualan CS" subtitle="Isi data diri, SPK, dan tagihan tim CS. Definisi angka sama dengan Dashboard dan Data Manajemen SW.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" refresh="refreshData" refresh-label="Hitung Ulang" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate">
        <span>Dibanding {{ \Carbon\Carbon::parse($prevRange[0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($prevRange[1])->translatedFormat('j M Y') }}</span>
    </x-ui.range-info>

    @foreach($sourceErrors as $error)
        <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-sm text-rose-700 dark:text-rose-400">
            {{ $error }} Angka yang memakai sumber ini ditampilkan "–".
        </div>
    @endforeach

    <div wire:loading.class="opacity-30" wire:target="setDateRange, refreshData" class="space-y-6 transition-opacity">
        {{-- Alur 1: 4 kartu penjualan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($cards as $no => $card)
                <x-ui.stat-card wire:key="card-{{ $no }}" compact
                    :label="$card['nama']" :value="$card['nilai']" :icon="$card['icon']"
                    :badge="$card['badge']" :badge-tone="$card['tone']"
                    :info-call="$showFormula ? 'showFormula(' . $no . ')' : null"
                    :note="$card['singkat'] . ($no === 29 ? ' Nilai per pasang: ' . $perPasang . '.' : '') . ' Bulan lalu: ' . $card['lalu'] . '.'" />
            @endforeach
        </div>

        {{-- Alur 2: leaderboard per CS --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 flex flex-col md:flex-row md:items-start justify-between gap-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Peringkat CS</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Diurutkan dari omset tagihan terbesar. % chat jadi SPK per CS belum bisa dihitung karena data chat Sleekflow belum terpisah per CS.</p>
                </div>
                <x-ui.btn variant="secondary" icon="document" wire:click="exportCsv" target="exportCsv" class="shrink-0">Unduh CSV</x-ui.btn>
            </div>
            @if($leaderboard === null)
                <p class="px-6 py-10 text-sm text-center text-amber-600 dark:text-amber-400">Menunggu data per CS dari sistem pusat (field baru). Data per CS yang lama tidak dipakai karena definisinya berbeda dengan Kamus.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                <th class="px-6 py-3">#</th>
                                <th class="px-6 py-3">CS</th>
                                <th class="px-6 py-3 text-right whitespace-nowrap">Isi Data Diri</th>
                                <th class="px-6 py-3 text-right whitespace-nowrap">SPK Closing</th>
                                <th class="px-6 py-3 text-right">Tagihan</th>
                                <th class="px-6 py-3 text-right whitespace-nowrap">Omset Tagihan</th>
                                <th class="px-6 py-3 text-right whitespace-nowrap">Sepatu / Tagihan</th>
                                <th class="px-6 py-3 text-right whitespace-nowrap">Nilai / Tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($leaderboard as $i => $row)
                                <tr wire:key="cs-{{ $i }}-{{ $row['nama'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="px-6 py-3 font-black text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ $row['nama'] }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300">{{ $num($row['leads']) }}</td>
                                    <td class="px-6 py-3 text-right font-bold text-slate-800 dark:text-gray-100">{{ $num($row['spk']) }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300">{{ $num($row['tagihan']) }}</td>
                                    <td class="px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ $rp($row['omset']) }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300">{{ $row['per_tagihan'] === null ? '–' : number_format($row['per_tagihan'], 2, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['nilai_tagihan']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-10 text-center text-sm italic text-slate-400">Belum ada penjualan CS pada rentang ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Alur 3: tabel harian --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Rincian Harian</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Tanggal = tanggal formulir, SPK, atau tagihan dibuat. Biaya iklan Meta tanpa pajak.
                    @unless($daily['lengkap'])
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Isi data diri, SPK, dan tagihan per hari menunggu field baru dari sistem pusat.</span>
                    @endunless
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Isi Data Diri</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">SPK Closing</th>
                            <th class="px-6 py-3 text-right">Tagihan</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Omset Tagihan</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Nilai / Tagihan</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Biaya Iklan</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">Biaya / SPK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($daily['rows'] as $row)
                            <tr wire:key="day-{{ $row['tanggal'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-3 font-medium text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300">{{ $num($row['leads']) }}</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-800 dark:text-gray-100">{{ $num($row['spk']) }}</td>
                                <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300">{{ $num($row['tagihan']) }}</td>
                                <td class="px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ $rp($row['omset']) }}</td>
                                <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['nilai_tagihan']) }}</td>
                                <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['biaya']) }}</td>
                                <td class="px-6 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['biaya_spk']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
