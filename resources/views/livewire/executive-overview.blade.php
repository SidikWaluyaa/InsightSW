@php
    $rp = fn ($v) => $v === null ? '–' : 'Rp ' . number_format($v, 0, ',', '.');
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $pct = fn ($a, $b) => $a === null || ! $b ? null : $a / $b * 100;
    $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];
    // Badge MoM: "▲ 4,2% vs bulan lalu" dengan warna lampu
    $momBadge = fn (array $c) => $c['perubahan'] === null ? null : ($c['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($c['perubahan']), 1, ',', '.') . '% vs bulan lalu';
    $momTone = fn (array $c) => $tones[$c['status']] ?? 'slate';
@endphp

<div class="space-y-6">
    {{-- Modal rumus (tombol "i", hanya Admin); di luar blok yang di-render ulang agar Alpine tidak rusak --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
    @endassets

    @if(! $range)
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl text-sm text-slate-500 dark:text-gray-400">
            Belum ada data untuk rentang ini. Ringkasan dihitung sampai kemarin (H-1), jadi rentang yang hanya berisi hari ini baru tersedia besok.
        </div>
    @else
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <p class="text-xs text-slate-500 dark:text-gray-400 max-w-3xl leading-relaxed">
                Lampu membandingkan dengan periode yang sama bulan lalu ({{ \Carbon\Carbon::parse($prevRange[0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($prevRange[1])->translatedFormat('j M Y') }}); omset dan SPK dibandingkan per hari.
                @if($trimmed)
                    <span class="font-semibold text-amber-600 dark:text-amber-400">Dihitung sampai {{ \Carbon\Carbon::parse($range[1])->translatedFormat('j M Y') }} (H-1), karena data Sleekflow hari ini belum tersedia.</span>
                @endif
            </p>
            <x-ui.btn variant="secondary" icon="refresh" wire:click="refreshData" target="refreshData" class="shrink-0">Hitung Ulang</x-ui.btn>
        </div>

        @foreach($sourceErrors as $error)
            <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-sm text-rose-700 dark:text-rose-400">
                {{ $error }} Angka yang memakai sumber ini ditampilkan "–".
            </div>
        @endforeach

        <div wire:loading.class="opacity-50" wire:target="refreshData" class="space-y-6 transition-opacity">
            {{-- Alur 1: 4 kartu utama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <x-ui.stat-card label="Total Omset (Net)" :value="$rp($cards['omset']['nilai'])" icon="cash" compact
                    :info-call="$showFormula ? 'showFormula(\'omset\')' : null"
                    :badge="$momBadge($cards['omset'])" :badge-tone="$momTone($cards['omset'])"
                    :note="'Nilai jasa di tagihan valid, sudah dikurangi diskon, tanpa ongkir. Bulan lalu: ' . $rp($cards['omset']['lalu']) . '.'" />
                <x-ui.stat-card label="SPK Closing" :value="$num($cards['spk']['nilai'])" unit="pasang" icon="clipboard-list"
                    :info-call="$showFormula ? 'showFormula(\'spk\')' : null"
                    :badge="$momBadge($cards['spk'])" :badge-tone="$momTone($cards['spk'])"
                    :note="'± ' . number_format(($cards['spk']['nilai'] ?? 0) / $days, 1, ',', '.') . ' pasang/hari. Tanpa SPK garansi -GR' . ($intake && $intake['rd'] !== null ? ' dan R&D' : '') . '. Bulan lalu: ' . $num($cards['spk']['lalu']) . '.'" />
                <x-ui.stat-card label="ROAS (MER)" :value="$cards['roas']['nilai'] === null ? '–' : number_format($cards['roas']['nilai'], 2, ',', '.') . 'x'" icon="chart"
                    :info-call="$showFormula ? 'showFormula(\'roas\')' : null"
                    :badge="$momBadge($cards['roas'])" :badge-tone="$momTone($cards['roas'])"
                    :note="'Omset ÷ biaya iklan Meta (tanpa pajak). Semua omset dianggap dari iklan. Bulan lalu: ' . ($cards['roas']['lalu'] === null ? '–' : number_format($cards['roas']['lalu'], 2, ',', '.') . 'x') . '.'" />
                <x-ui.stat-card label="Tingkat Akurasi Data" value="Menyusul" icon="check-circle" compact
                    :info-call="$showFormula ? 'showFormula(\'akurasi\')' : null"
                    note="Kecocokan data sistem dengan kondisi lapangan. Rumus dan cara pengecekannya masih didiskusikan dengan Tim Data." />
            </div>

            {{-- Alur 2: corong chat sampai sepatu tiba --}}
            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-5">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Corong Chat sampai Sepatu Tiba</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Persen di bawah tiap tahap = dibanding tahap sebelumnya. Status Hot / Warm / Cold menyusul (belum ada datanya).</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @foreach($funnel as $i => $step)
                        @php $fromPrev = $i > 0 ? $pct($step['nilai'], $funnel[$i - 1]['nilai']) : null; @endphp
                        <div class="relative rounded-2xl p-4 border border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/30">
                            <p class="flex items-center gap-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">
                                <span>{{ $i + 1 }}. {{ $step['label'] }}</span>
                                @if($showFormula)<x-ui.info-button :call="'showFormula(\'funnel_' . ($i + 1) . '\')'" :label="$step['label']" />@endif
                            </p>
                            <p class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $num($step['nilai']) }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1">{{ $step['ket'] }}</p>
                            @if($fromPrev !== null)
                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($fromPrev, 1, ',', '.') }}% dari tahap {{ $i }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600 dark:text-gray-300">
                    <span>Chat-to-SPK keseluruhan: <span class="font-bold">{{ $funnelSummary['chat_to_spk'] === null ? '–' : number_format($funnelSummary['chat_to_spk'], 2, ',', '.') . '%' }}</span></span>
                    <span>Nilai deal (omset tagihan): <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $rp($funnelSummary['omset']) }}</span></span>
                </div>
            </section>

            {{-- Alur 3: tren omset vs biaya iklan (kiri) dan SPK per jenis (kanan) --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Tren Omset vs Biaya Iklan (Harian)</h3>
                            @if($chart['revenue'] === null)
                                <p class="text-xs text-amber-600 dark:text-amber-400">Omset harian menyusul (menunggu field baru dari sistem pusat). Sementara hanya biaya iklan.</p>
                            @else
                                <p class="text-xs text-slate-500 dark:text-gray-400">Omset per tanggal tagihan dibuat; biaya iklan Meta tanpa pajak.</p>
                            @endif
                        </div>
                        @if($projection)
                            <div class="text-right">
                                <p class="flex items-center justify-end gap-1.5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">
                                    <span>Proyeksi Omset Akhir Bulan</span>
                                    @if($showFormula)<x-ui.info-button call="showFormula('proyeksi')" label="Proyeksi Omset Akhir Bulan" />@endif
                                </p>
                                <p class="text-xl font-black text-slate-800 dark:text-white">{{ $rp($projection['nilai']) }}</p>
                                @if($projection['bulan_lalu'])
                                    <p class="text-[11px] text-slate-500 dark:text-gray-400">{{ $projection['nama_bulan_lalu'] }}: {{ $rp($projection['bulan_lalu']) }}
                                        ({{ $projection['nilai'] >= $projection['bulan_lalu'] ? '▲' : '▼' }} {{ number_format(abs($projection['nilai'] / $projection['bulan_lalu'] * 100 - 100), 1, ',', '.') }}%)</p>
                                @endif
                            </div>
                        @endif
                    </div>
                    {{-- wire:key ikut rentang: elemen diganti saat rentang berubah, jadi Alpine menggambar ulang grafiknya --}}
                    <div wire:key="exec-chart-{{ $range[0] }}-{{ $range[1] }}" wire:ignore
                        x-data="{ chart: null }"
                        x-init="
                            const data = @js($chart);
                            const series = [{ name: 'Biaya iklan', data: data.spend }];
                            if (data.revenue) series.unshift({ name: 'Omset', data: data.revenue });
                            chart = new ApexCharts($refs.canvas, {
                                chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
                                theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
                                series,
                                colors: data.revenue ? ['#3b82f6', '#f59e0b'] : ['#f59e0b'],
                                xaxis: { categories: data.labels },
                                yaxis: { min: 0, labels: { formatter: v => v >= 1e6 ? 'Rp ' + (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : 'Rp ' + Math.round(v / 1000).toLocaleString('id-ID') + ' rb' } },
                                tooltip: { y: { formatter: v => 'Rp ' + Math.round(v).toLocaleString('id-ID') } },
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 2 },
                                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                                legend: { position: 'top', horizontalAlign: 'left' },
                            });
                            chart.render();
                        "
                        x-on:livewire:navigating.window="chart && chart.destroy()">
                        <div x-ref="canvas"></div>
                    </div>
                </section>

                <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <div>
                        <h3 class="flex items-center gap-2 font-bold text-lg text-slate-800 dark:text-white">
                            {{ ($intake['mode'] ?? 'spk') === 'fisik' ? 'Status Sepatu Masuk' : 'SPK Terbit per Jenis' }}
                            @if($showFormula)<x-ui.info-button call="showFormula('intake')" label="Sepatu masuk per jenis" />@endif
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-gray-400">
                            {{ ($intake['mode'] ?? 'spk') === 'fisik' ? 'Sepatu yang fisiknya tiba di gudang pada rentang ini, menurut tanggal tiba.' : 'SPK yang dibuat pada rentang ini, dibaca dari nomor SPK. Rincian sepatu yang fisiknya tiba per jenis menyusul.' }}
                        </p>
                    </div>
                    @if(! $intake)
                        <p class="text-sm text-slate-400">Data SPK dari sistem pusat belum tersedia.</p>
                    @else
                        @php
                            $rows = [
                                ['Berbayar (reguler)', $intake['berbayar'], 'bg-emerald-500'],
                                ['Klaim garansi (-GR)', $intake['garansi'], 'bg-amber-500'],
                                ['Riset (R&D, nomor RD-)', $intake['rd'], 'bg-violet-500'],
                            ];
                        @endphp
                        <div class="space-y-3">
                            @foreach($rows as [$label, $value, $bar])
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600 dark:text-gray-300">{{ $label }}</span>
                                        @if($value === null)
                                            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">Menyusul</span>
                                        @else
                                            <span class="font-bold text-slate-800 dark:text-white">{{ $num($value) }} <span class="text-xs font-medium text-slate-400">({{ number_format($pct($value, $intake['total']) ?? 0, 1, ',', '.') }}%)</span></span>
                                        @endif
                                    </div>
                                    <div class="h-1.5 mt-1 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                        <div class="h-full {{ $bar }}" style="width: {{ $value === null ? 0 : min($pct($value, $intake['total']) ?? 0, 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1 text-sm">
                            <div class="flex justify-between"><span class="font-bold text-slate-700 dark:text-gray-200">{{ $intake['mode'] === 'fisik' ? 'Total fisik pintu gudang' : 'Total SPK terbit' }}</span><span class="font-black text-slate-800 dark:text-white">{{ $num($intake['total']) }}</span></div>
                            @if($intake['mode'] === 'spk')
                                <div class="flex justify-between text-xs text-slate-500 dark:text-gray-400"><span>Sepatu fisik tiba di periode (semua jenis)</span><span>{{ $num($intake['tiba']) }}</span></div>
                            @endif
                        </div>
                        @if($intake['rd'] === null)
                            <p class="text-[11px] text-slate-400 dark:text-gray-500">Jumlah R&D menunggu field baru dari sistem pusat; sementara masih ikut di baris Berbayar.</p>
                        @endif
                    @endif
                </section>
            </div>
        </div>
    @endif
</div>
