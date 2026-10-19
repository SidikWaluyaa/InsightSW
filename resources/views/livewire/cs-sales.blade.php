@php
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $rp = fn ($v) => $v === null ? '–' : 'Rp ' . number_format($v, 0, ',', '.');
    $th = 'px-4 py-3 whitespace-nowrap';
    // Header leaderboard boleh dua baris supaya 7 kolom muat tanpa geser
    $thWrap = 'px-4 py-3 leading-tight';
@endphp

{{-- Dashboard CS: layout PRD halaman 3 (CS Sales Pipeline & Closing). Definisi angka = Kamus Metrik v2.0. --}}
<div class="space-y-6">
    {{-- Modal rumus (tombol "i", hanya Admin) --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    <x-ui.page-header title="Dashboard CS" subtitle="Pipeline penjualan & closing tim CS: biodata, SPK, invoice, dan omset deal.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" refresh="refreshData" refresh-label="Hitung Ulang" />
    </x-ui.page-header>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <x-ui.range-info :start="$startDate" :end="$endDate">
            <span>Dibanding {{ \Carbon\Carbon::parse($prevRange[0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($prevRange[1])->translatedFormat('j M Y') }}</span>
        </x-ui.range-info>
        <div class="flex items-center gap-2">
            <x-ui.btn variant="secondary" icon="document" wire:click="exportCsv" target="exportCsv">Export CSV</x-ui.btn>
            <x-ui.btn variant="secondary" icon="document" :href="route('cs-sales.export-pdf', ['dari' => $startDate, 'sampai' => $endDate])" x-data x-on:click.prevent="window.open($el.href, '_blank')">Export PDF</x-ui.btn>
        </div>
    </div>

    @foreach($sourceErrors as $error)
        <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-sm text-rose-700 dark:text-rose-400">
            {{ $error }} Angka yang memakai sumber ini ditampilkan "–".
        </div>
    @endforeach

    <div wire:loading.class="opacity-30" wire:target="setDateRange, refreshData" class="space-y-6 transition-opacity">
        {{-- Alur 1: 4 kartu penjualan utama --}}
        @php
            $prd = [
                20 => ['label' => 'Closing Biodata', 'unit' => 'kontak', 'note' => 'Formulir data diri customer (CRM) yang masuk. Bulan lalu: ' . $cards[20]['lalu'] . '.'],
                27 => ['label' => 'Chat-to-SPK CR', 'unit' => null, 'note' => 'SPK closing ÷ chat masuk. Baseline bulan lalu: ' . $cards[27]['lalu'] . '.'],
                29 => ['label' => 'Average Order Value', 'unit' => '/ invoice', 'note' => 'ADV: ' . $perPasang . ' / pasang. Bulan lalu: ' . $cards[29]['lalu'] . ' / invoice.'],
                26 => ['label' => 'Multi-Pair Ratio', 'unit' => null, 'note' => 'Rata-rata pasang sepatu per nota (invoice). Bulan lalu: ' . $cards[26]['lalu'] . '.'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($prd as $no => $c)
                <x-ui.stat-card wire:key="card-{{ $no }}" compact
                    :label="$c['label']" :value="$cards[$no]['nilai']" :unit="$c['unit']" :icon="$cards[$no]['icon']"
                    :badge="$cards[$no]['badge']" :badge-tone="$cards[$no]['tone']"
                    :info-call="$showFormula ? 'showFormula(' . $no . ')' : null"
                    :note="$c['note']" />
            @endforeach
        </div>

        {{-- Alur 2: leaderboard kinerja penjualan CS (ranking omset riil) --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Leaderboard Kinerja Penjualan CS</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">Ranking omset riil (invoice valid, net diskon, tanpa ongkir). Chat-to-SPK per CS belum bisa dihitung karena data chat Sleekflow belum terpisah per agen. Akun non-CS (Finance, Ai Jahit) tidak ditampilkan.</p>
            </div>
            @if($leaderboard === null)
                <p class="px-6 py-10 text-sm text-center text-amber-600 dark:text-amber-400">Menunggu data per CS dari sistem pusat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                <th class="{{ $thWrap }}">Rank</th>
                                <th class="{{ $thWrap }}">Nama Agen CS</th>
                                <th class="{{ $thWrap }} text-right">SPK Terbit</th>
                                <th class="{{ $thWrap }} text-right">Total Invoices</th>
                                <th class="{{ $thWrap }} text-right">Omset Deal Pipeline (Net)</th>
                                <th class="{{ $thWrap }} text-right">Chat-to-SPK CR</th>
                                <th class="{{ $thWrap }} text-right">Multi-Pair Ratio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($leaderboard as $i => $row)
                                <tr wire:key="cs-{{ $i }}-{{ $row['nama'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-black text-slate-400">
                                        {{-- Medali untuk 3 teratas (PRD) --}}
                                        @if($i < 3)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] text-white {{ ['bg-yellow-500', 'bg-slate-400', 'bg-amber-700'][$i] }}" title="Peringkat {{ $i + 1 }}">{{ $i + 1 }}</span>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ $row['nama'] }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700 dark:text-gray-200 whitespace-nowrap">{{ $num($row['spk']) }} psg</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $num($row['tagihan']) }} inv</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ $rp($row['omset']) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-400" title="Belum ada data chat per agen">–</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300">{{ $row['per_tagihan'] === null ? '–' : number_format($row['per_tagihan'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-10 text-center text-sm italic text-slate-400">Belum ada penjualan CS pada rentang ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Alur 3: tabel rincian transaksi harian (format Kamus Metrik v2.0) --}}
        <section x-data="{ semua: false }" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Rincian Transaksi Harian</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Tanggal = tanggal biodata, SPK, atau invoice dibuat. Cost per SPK = biaya iklan Meta (tanpa pajak) ÷ SPK terbit.
                    @unless($daily['lengkap'])
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Biodata, SPK, dan invoice per hari menunggu data dari sistem pusat.</span>
                    @endunless
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                            <th class="{{ $th }}">Tanggal</th>
                            <th class="{{ $th }} text-right">Biodata (CRM)</th>
                            <th class="{{ $th }} text-right">SPK Terbit</th>
                            <th class="{{ $th }} text-right">Invoices Valid</th>
                            <th class="{{ $th }} text-right">Deal Pipeline Net</th>
                            <th class="{{ $th }} text-right">AOV Bersih</th>
                            <th class="{{ $th }} text-right">Cost per SPK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($daily['rows'] as $i => $row)
                            {{-- 7 hari terbaru tampil dulu; sisanya lewat tombol "Selengkapnya" (PRD) --}}
                            <tr wire:key="day-{{ $row['tanggal'] }}" @if($i >= 7) x-show="semua" x-cloak @endif class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <td class="px-4 py-3 font-medium text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $row['leads'] === null ? '–' : $num($row['leads']) . ' kontak' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-gray-100 whitespace-nowrap">{{ $row['spk'] === null ? '–' : $num($row['spk']) . ' SPK' }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $row['tagihan'] === null ? '–' : $num($row['tagihan']) . ' inv' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ $rp($row['omset']) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['nilai_tagihan']) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $rp($row['biaya_spk']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($daily['rows']) > 7)
                <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" x-on:click="semua = !semua" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline"
                        x-text="semua ? 'Tampilkan 7 hari terakhir saja' : 'Selengkapnya… tampilkan {{ count($daily['rows']) }} hari'"></button>
                </div>
            @endif
        </section>
    </div>
</div>
