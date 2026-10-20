<div class="space-y-6">
    <x-ui.page-header title="Pusat Komando" subtitle="Divisi Gudang • Ringkasan operasional dan performa QC.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :presets="$this->datePresets()" refresh="syncNow" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate" :updated="$lastSync" />

    <div class="relative p-6 space-y-8 bg-slate-950 rounded-3xl text-slate-200 font-sans selection:bg-emerald-500/30 overflow-hidden">
    {{-- Decorative Background Elements --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-emerald-500/5 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-500/5 rounded-full blur-[120px]"></div>
    </div>

    {{-- PRIMARY METRICS --}}
    <div class="relative grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $primaryStats = [
                [
                    'label' => 'Keterisian Rak',
                    'value' => $this->warehouseStats['total_sepatu_dirak'],
                    'unit' => 'Slot Terpakai',
                    'sub' => 'Sebelum + Sesudah Reparasi',
                    'color' => 'indigo',
                    'icon' => '<path d="M21 16.5c0 .38-.21.71-.53.88l-7.97 4.44c-.31.17-.69.17-1 0l-7.97-4.44c-.32-.17-.53-.5-.53-.88v-9c0-.38.21-.71.53-.88l7.97-4.44c.31-.17.69-.17 1 0l7.97 4.44c.32.17.53.5.53.88v9z"/>'
                ],
                [
                    'label' => 'Selesai Dikerjakan',
                    'value' => $this->warehouseStats['total_sepatu_finish_periode'],
                    'unit' => 'SPK',
                    'sub' => 'Selama Periode',
                    'color' => 'emerald',
                    'icon' => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>'
                ],
                [
                    'label' => 'Sepatu Masuk',
                    'value' => $this->warehouseStats['total_sepatu_diterima_periode'],
                    'unit' => 'SPK',
                    'sub' => 'Selama Periode',
                    'color' => 'amber',
                    'icon' => '<path d="M11 9h2V7h-2v2zm0 4h2v-2h-2v2zm0 4h2v-2h-2v2zm-6 4h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2zM5 5h14v14H5V5z"/>'
                ],
                [
                    'label' => 'Dikirim ke Workshop',
                    'value' => $this->warehouseStats['total_spk_print'],
                    'unit' => 'SPK',
                    'sub' => 'Selama Periode',
                    'color' => 'slate',
                    'icon' => '<path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>'
                ]
            ];

            // Class ditulis utuh: Tailwind tidak membuat class yang dirangkai dinamis (text-{warna}-400)
            $primaryColors = [
                'indigo' => ['label' => 'text-indigo-400', 'icon' => 'text-indigo-400', 'bar' => 'bg-indigo-500'],
                'emerald' => ['label' => 'text-emerald-400', 'icon' => 'text-emerald-400', 'bar' => 'bg-emerald-500'],
                'amber' => ['label' => 'text-amber-400', 'icon' => 'text-amber-400', 'bar' => 'bg-amber-500'],
                'slate' => ['label' => 'text-slate-400', 'icon' => 'text-slate-400', 'bar' => 'bg-slate-500'],
            ];
        @endphp

        @foreach($primaryStats as $stat)
        @php $c = $primaryColors[$stat['color']]; @endphp
        <div class="group relative bg-slate-900/40 border border-slate-800 rounded-3xl p-6 hover:bg-slate-900/60 transition-all duration-500 overflow-hidden shadow-xl shadow-black/20">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 group-hover:scale-125 transition-all duration-700 {{ $c['icon'] }}">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">{!! $stat['icon'] !!}</svg>
            </div>
            
            <div class="relative z-10">
                <p class="text-[10px] font-black {{ $c['label'] }} uppercase tracking-[0.3em] mb-4">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-5xl font-black text-white leading-none tracking-tighter">{{ number_format($stat['value'], 0, ',', '.') }}</h3>
                    <span class="text-[11px] font-bold text-slate-500 uppercase">{{ $stat['unit'] }}</span>
                </div>
                <div class="mt-6 flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full {{ $c['bar'] }}"></div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $stat['sub'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SECONDARY STATS (Snapshots) --}}
    {{-- Diverifikasi lewat API: empat angka ini (dan "Sepatu di Rak" di atas) tidak berubah saat rentang tanggal diganti --}}
    <div class="relative flex flex-wrap items-center gap-2">
        <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Kondisi Saat Ini</h3>
        <span class="px-2 py-0.5 rounded-md border border-slate-700 bg-slate-900 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tidak mengikuti filter tanggal</span>
        <span class="text-[11px] text-slate-500">Posisi terkini saat data diambil. "Keterisian Rak" di atas juga posisi terkini (rak penerimaan + rak selesai); tiga kartu lainnya dan grafik QC mengikuti periode.</span>
    </div>
    <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $snapshotStats = [
                ['label' => 'SPK Pending', 'value' => $summary['pending_reception'] ?? 0, 'unit' => 'SPK', 'color' => 'amber'],
                ['label' => 'Selesai, Belum di Rak', 'value' => $summary['finished_not_stored'] ?? 0, 'unit' => 'SPK', 'color' => 'blue'],
                ['label' => 'Pengiriman Belum Diverifikasi', 'value' => $summary['shipping_pending'] ?? 0, 'unit' => 'Pengiriman', 'color' => 'indigo'],
                ['label' => 'Siap Diambil', 'value' => $summary['ready_for_pickup'] ?? 0, 'unit' => 'SPK', 'color' => 'emerald'],
            ];

            $snapshotColors = [
                'amber' => ['unit' => 'text-amber-500', 'box' => 'bg-amber-500/10 text-amber-500'],
                'blue' => ['unit' => 'text-blue-500', 'box' => 'bg-blue-500/10 text-blue-500'],
                'indigo' => ['unit' => 'text-indigo-500', 'box' => 'bg-indigo-500/10 text-indigo-500'],
                'emerald' => ['unit' => 'text-emerald-500', 'box' => 'bg-emerald-500/10 text-emerald-500'],
            ];
        @endphp

        @foreach($snapshotStats as $stat)
        @php $c = $snapshotColors[$stat['color']]; @endphp
        <div class="bg-slate-900/20 border border-slate-800/60 p-5 rounded-2xl flex items-center justify-between group hover:border-slate-700 transition-colors">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-white">{{ number_format($stat['value'], 0, ',', '.') }}</span>
                    <span class="text-[9px] font-bold {{ $c['unit'] }} uppercase">{{ $stat['unit'] }}</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $c['box'] }} flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
        @endforeach
    </div>

    {{-- QC VISUALIZATION --}}
    <div class="relative grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3 bg-slate-900/40 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Tren QC Penerimaan Gudang</h3>
                    <p class="text-[10px] text-slate-500 uppercase font-black tracking-[0.2em] mt-1">SPK lolos dan ditolak saat diperiksa di penerimaan gudang, per hari</p>
                </div>
                <div class="flex items-center gap-6 p-2 bg-slate-950/50 rounded-xl border border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(129,140,248,0.4)]"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">QC Lolos</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(251,113,133,0.4)]"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">QC Reject</span>
                    </div>
                </div>
            </div>
            <div class="h-[350px] relative" wire:ignore>
                <canvas id="qcTrendsChart"></canvas>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-50"></div>
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mb-8 w-full text-center">Komposisi QC</h3>
            
            <div class="relative w-full aspect-square max-w-[220px]">
                <div class="absolute inset-0" wire:ignore>
                    <canvas id="qcCompositionChart"></canvas>
                </div>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest">Total</p>
                    <p class="text-4xl font-black text-white tracking-tighter">{{ number_format(($qcAnalytics['stats']['data'][0] ?? 0) + ($qcAnalytics['stats']['data'][1] ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-8 w-full">
                <div class="text-center group">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Lolos</p>
                    <p class="text-2xl font-black text-white group-hover:scale-110 transition-transform">{{ number_format($qcAnalytics['stats']['data'][0] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="text-center group">
                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Reject</p>
                    <p class="text-2xl font-black text-white group-hover:scale-110 transition-transform">{{ number_format($qcAnalytics['stats']['data'][1] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- External Resources --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let trendsChart = null;
        let compositionChart = null;

        const initialQcData = @json($qcAnalytics);

        document.addEventListener('livewire:navigated', () => initCharts(initialQcData));
        document.addEventListener('DOMContentLoaded', () => initCharts(initialQcData));

        // Gambar ulang dengan data baru setiap kali rentang tanggal berubah / data ditarik ulang
        document.addEventListener('livewire:init', () => {
            Livewire.on('warehouse-charts-update', ({ qc }) => initCharts(qc));
        });

        function initCharts(qcData) {
            if (!qcData || !qcData.trends || typeof Chart === 'undefined') return;

            const ctxTrends = document.getElementById('qcTrendsChart');
            if (ctxTrends) {
                if (trendsChart) trendsChart.destroy();
                trendsChart = new Chart(ctxTrends, {
                    type: 'line',
                    data: {
                        labels: qcData.trends.labels,
                        datasets: [
                            {
                                label: 'QC Lolos',
                                data: qcData.trends.lolos,
                                borderColor: '#818cf8',
                                backgroundColor: 'rgba(129, 140, 248, 0.05)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 4,
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointHoverBackgroundColor: '#818cf8',
                                pointHoverBorderColor: '#fff',
                                pointHoverBorderWidth: 2
                            },
                            {
                                label: 'QC Reject',
                                data: qcData.trends.reject,
                                borderColor: '#fb7185',
                                backgroundColor: 'rgba(251, 113, 133, 0.05)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 4,
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointHoverBackgroundColor: '#fb7185',
                                pointHoverBorderColor: '#fff',
                                pointHoverBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(51, 65, 85, 0.3)', drawBorder: false },
                                ticks: { color: '#475569', font: { size: 10, weight: '900' }, padding: 10 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#475569', font: { size: 10, weight: '900' }, padding: 10 }
                            }
                        }
                    }
                });
            }

            const ctxComposition = document.getElementById('qcCompositionChart');
            if (ctxComposition) {
                if (compositionChart) compositionChart.destroy();
                compositionChart = new Chart(ctxComposition, {
                    type: 'doughnut',
                    data: {
                        labels: ['QC Lolos', 'QC Reject'],
                        datasets: [{
                            data: (qcData.stats && qcData.stats.data) || [0, 0],
                            backgroundColor: ['#818cf8', '#fb7185'],
                            borderWidth: 0,
                            hoverOffset: 15,
                            borderRadius: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '85%',
                        plugins: { legend: { display: false } },
                        animation: { animateScale: true, animateRotate: true }
                    }
                });
            }
        }
    </script>
</div>
