<div class="p-6 space-y-8 bg-slate-950 min-h-screen text-slate-200 font-sans selection:bg-emerald-500/30" x-data="{ 
    syncing: @entangle('isLoading')
}">
    {{-- Decorative Background Elements --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-emerald-500/5 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-500/5 rounded-full blur-[120px]"></div>
    </div>

    {{-- Header Section --}}
    <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-2">
        <div>
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-900/20 text-white transform hover:rotate-6 transition-transform duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-white uppercase leading-none">Pusat Komando</h2>
                    <p class="text-slate-500 text-xs font-bold tracking-[0.3em] uppercase mt-1">Divisi Gudang • Intelijen Operasional</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            {{-- Quick Filter Buttons --}}
            <div class="flex items-center bg-slate-900 border border-slate-800 p-1 rounded-2xl shadow-2xl">
                <button wire:click="setRange('today')" 
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $startDate == now()->toDateString() && $endDate == now()->toDateString() ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/40' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800' }}">
                    Hari Ini
                </button>
                <button wire:click="setRange('7days')" 
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $startDate == now()->subDays(7)->toDateString() ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/40' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800' }}">
                    7 Hari
                </button>
                <button wire:click="setRange('30days')" 
                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $startDate == now()->subDays(30)->toDateString() ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/40' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800' }}">
                    30 Hari
                </button>
            </div>

            {{-- Unified Date Picker --}}
            <div class="relative group"
                x-data="{
                    picker: null,
                    init() {
                        this.picker = flatpickr($refs.picker, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            defaultDate: ['{{ $startDate }}', '{{ $endDate }}'],
                            onChange: (selectedDates) => {
                                if (selectedDates.length === 2) {
                                    const start = this.picker.formatDate(selectedDates[0], 'Y-m-d');
                                    const end = this.picker.formatDate(selectedDates[1], 'Y-m-d');
                                    @this.set('startDate', start);
                                    @this.set('endDate', end);
                                }
                            }
                        });
                    }
                }">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 pointer-events-none text-emerald-500 z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 00-2 2z" />
                    </svg>
                </div>
                <input x-ref="picker" readonly
                    class="bg-slate-900 border border-slate-800 rounded-2xl text-xs font-black text-white focus:ring-2 focus:ring-emerald-500/40 pl-14 pr-8 py-4 uppercase tracking-[0.2em] cursor-pointer shadow-2xl transition-all min-w-[320px] text-center"
                    placeholder="PILIH RENTANG TANGGAL">
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest leading-none mb-1">Update Terakhir</p>
                    <div class="flex items-center gap-2 justify-end">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-xs font-mono font-bold text-emerald-400">{{ $lastSync ?? '--:--:--' }}</span>
                    </div>
                </div>
                
                <button wire:click="syncNow" wire:loading.attr="disabled"
                    class="relative group px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-black transition-all duration-300 shadow-2xl shadow-emerald-900/40 active:scale-95 overflow-hidden">
                    <div class="relative z-10 flex items-center gap-3">
                        <svg wire:loading.remove wire:target="syncNow" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg wire:loading wire:target="syncNow" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="uppercase tracking-[0.2em] text-[10px]">Tarik Data</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    {{-- PRIMARY METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $primaryStats = [
                [
                    'label' => 'Sepatu di Rak',
                    'value' => $this->warehouseStats['total_sepatu_dirak'],
                    'unit' => 'Unit',
                    'sub' => 'Inventori Aktif',
                    'color' => 'indigo',
                    'icon' => '<path d="M21 16.5c0 .38-.21.71-.53.88l-7.97 4.44c-.31.17-.69.17-1 0l-7.97-4.44c-.32-.17-.53-.5-.53-.88v-9c0-.38.21-.71.53-.88l7.97-4.44c.31-.17.69-.17 1 0l7.97 4.44c.32.17.53.5.53.88v9z"/>'
                ],
                [
                    'label' => 'Selesai Periode',
                    'value' => $this->warehouseStats['total_sepatu_finish_periode'],
                    'unit' => 'Unit',
                    'sub' => 'Aliran Keluar',
                    'color' => 'emerald',
                    'icon' => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>'
                ],
                [
                    'label' => 'Masuk Periode',
                    'value' => $this->warehouseStats['total_sepatu_diterima_periode'],
                    'unit' => 'Unit',
                    'sub' => 'Aliran Masuk',
                    'color' => 'amber',
                    'icon' => '<path d="M11 9h2V7h-2v2zm0 4h2v-2h-2v2zm0 4h2v-2h-2v2zm-6 4h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2zM5 5h14v14H5V5z"/>'
                ],
                [
                    'label' => 'Total QC Lolos',
                    'value' => $this->warehouseStats['total_spk_print'],
                    'unit' => 'SPK',
                    'sub' => 'Total QC Lolos',
                    'color' => 'slate',
                    'icon' => '<path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>'
                ]
            ];
        @endphp

        @foreach($primaryStats as $stat)
        <div class="group relative bg-slate-900/40 border border-slate-800 rounded-3xl p-6 hover:bg-slate-900/60 transition-all duration-500 overflow-hidden shadow-xl shadow-black/20">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 group-hover:scale-125 transition-all duration-700 text-{{ $stat['color'] }}-400">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">{!! $stat['icon'] !!}</svg>
            </div>
            
            <div class="relative z-10">
                <p class="text-[10px] font-black text-{{ $stat['color'] }}-400/80 uppercase tracking-[0.3em] mb-4">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-5xl font-black text-white leading-none tracking-tighter">{{ number_format($stat['value']) }}</h3>
                    <span class="text-[11px] font-bold text-slate-500 uppercase">{{ $stat['unit'] }}</span>
                </div>
                <div class="mt-6 flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full bg-{{ $stat['color'] }}-500"></div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $stat['sub'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SECONDARY STATS (Snapshots) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $snapshotStats = [
                ['label' => 'SPK Pending', 'value' => $summary['pending_reception'] ?? 0, 'unit' => 'Antrean', 'color' => 'amber'],
                ['label' => 'Di Finish', 'value' => $summary['finished_not_stored'] ?? 0, 'unit' => 'Selesai', 'color' => 'blue'],
                ['label' => 'Antrean Kirim', 'value' => $summary['shipping_pending'] ?? 0, 'unit' => 'Paket', 'color' => 'indigo'],
                ['label' => 'Siap Diambil', 'value' => $summary['ready_for_pickup'] ?? 0, 'unit' => 'Offline', 'color' => 'emerald'],
            ];
        @endphp

        @foreach($snapshotStats as $stat)
        <div class="bg-slate-900/20 border border-slate-800/60 p-5 rounded-2xl flex items-center justify-between group hover:border-slate-700 transition-colors">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-white">{{ number_format($stat['value']) }}</span>
                    <span class="text-[9px] font-bold text-{{ $stat['color'] }}-500/70 uppercase">{{ $stat['unit'] }}</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-500/10 flex items-center justify-center text-{{ $stat['color'] }}-500 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
        @endforeach
    </div>

    {{-- QC VISUALIZATION --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3 bg-slate-900/40 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Tren Performa QC</h3>
                    <p class="text-[10px] text-slate-500 uppercase font-black tracking-[0.2em] mt-1">Analisis Batch & Aliran Keluar</p>
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
            <div class="h-[350px] relative">
                <canvas id="qcTrendsChart"></canvas>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-50"></div>
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mb-8 w-full text-center">Komposisi QC</h3>
            
            <div class="relative w-full aspect-square max-w-[220px]">
                <canvas id="qcCompositionChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest">Total</p>
                    <p class="text-4xl font-black text-white tracking-tighter">{{ number_format(($qcAnalytics['stats']['data'][0] ?? 0) + ($qcAnalytics['stats']['data'][1] ?? 0)) }}</p>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-8 w-full">
                <div class="text-center group">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Lolos</p>
                    <p class="text-2xl font-black text-white group-hover:scale-110 transition-transform">{{ number_format($qcAnalytics['stats']['data'][0] ?? 0) }}</p>
                </div>
                <div class="text-center group">
                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Reject</p>
                    <p class="text-2xl font-black text-white group-hover:scale-110 transition-transform">{{ number_format($qcAnalytics['stats']['data'][1] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- External Resources --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Flatpickr Custom Solid Dark Theme */
        .flatpickr-calendar {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5) !important;
            border-radius: 1rem !important;
            color: #f8fafc !important;
            width: 320px !important;
        }
        
        .flatpickr-months .flatpickr-month {
            background: #1e293b !important;
            color: #f8fafc !important;
            fill: #f8fafc !important;
            height: 50px !important;
        }

        .flatpickr-current-month {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            padding-top: 10px !important;
            color: #fff !important;
        }

        .flatpickr-monthDropdown-months {
            background: #1e293b !important;
            color: #fff !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            border: 1px solid #334155 !important;
            outline: none !important;
        }

        .flatpickr-monthDropdown-month {
            background: #1e293b !important;
            color: #fff !important;
        }

        .flatpickr-weekday {
            color: #94a3b8 !important;
            font-weight: 700 !important;
        }

        .flatpickr-day {
            color: #cbd5e1 !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
        }

        .flatpickr-day:hover {
            background: #334155 !important;
            color: #fff !important;
        }

        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #fff !important;
        }

        .flatpickr-day.inRange {
            background: rgba(16, 185, 129, 0.2) !important;
            box-shadow: none !important;
            color: #10b981 !important;
        }

        .flatpickr-calendar.arrowTop:after, .flatpickr-calendar.arrowTop:before {
            border-bottom-color: #1e293b !important;
        }
    </style>

    <script>
        document.addEventListener('livewire:navigated', () => initCharts());
        document.addEventListener('DOMContentLoaded', () => initCharts());
        window.addEventListener('swal', () => setTimeout(initCharts, 500));

        let trendsChart = null;
        let compositionChart = null;

        function initCharts() {
            const qcData = @json($qcAnalytics);
            if (!qcData || !qcData.trends) return;

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
                            data: qcData.stats.data,
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
