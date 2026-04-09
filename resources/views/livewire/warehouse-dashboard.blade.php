<div class="px-6 py-8 pb-24 bg-gray-50 dark:bg-gray-950 min-h-screen transition-colors duration-300 font-sans" 
     wire:poll.10s="loadData"
     x-data="{ 
        init() {
            // Dashboard initialized
        }
     }">

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#4E79A7] flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">Command Center</h2>
                    <p class="text-[9px] font-black text-[#4E79A7] uppercase tracking-[0.2em] mt-1">Warehouse Division</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="refresh" 
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-[#4E79A7] hover:bg-[#3b5e82] text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center gap-2">
                    <span wire:loading.remove wire:target="refresh">TARIK DATA LIVE</span>
                    <span wire:loading wire:target="refresh">SYNCING...</span>
                    <svg wire:loading.remove wire:target="refresh" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <svg wire:loading wire:target="refresh" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <div class="h-10 w-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>
                <div class="text-right">
                    <p class="text-[8px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Last Sync</p>
                    <p class="text-xs font-black text-slate-800 dark:text-white mt-0.5">{{ $lastUpdated }}</p>
                </div>
            </div>
        </div>
    </x-slot>
    
    {{-- Main Insights Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-10">
        {{-- Left: Branded Headline --}}
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-[40px] p-10 shadow-sm border border-gray-100 dark:border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-8">
                    <span class="px-4 py-1.5 bg-blue-500/10 text-[9px] font-black text-[#4E79A7] uppercase tracking-[0.2em] rounded-full border border-[#4E79A7]/20">Sistem Terkoneksi</span>
                    <div class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#4E79A7]"></span>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <h1 class="text-5xl font-black text-slate-800 dark:text-white tracking-tighter mb-4">Pusat Komando <span class="bg-gradient-to-r from-[#4E79A7] to-blue-400 bg-clip-text text-transparent italic">Operasional</span></h1>
                        <p class="text-slate-400 dark:text-gray-500 font-bold text-lg tracking-tight">Optimalisasi stok dan efisiensi gudang secara real-time.</p>
                    </div>
                    
                    {{-- Search moved to top row to balance layout --}}
                    <div class="relative group w-full md:w-80">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400 group-focus-within:text-[#4E79A7] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="CARI SPK / MEMBER..." 
                               class="w-full bg-gray-50 dark:bg-slate-800 border-none rounded-2xl py-4 pl-12 pr-6 text-sm font-bold text-slate-600 dark:text-gray-300 focus:ring-4 focus:ring-[#4E79A7]/10 placeholder-slate-300 dark:placeholder-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    @php
                        $metrics = [
                            ['label' => 'SPK Pending', 'value' => $data['summary']['pending_reception'] ?? 0, 'color' => 'slate'],
                            ['label' => 'Di Finish', 'value' => $data['summary']['finished_not_stored'] ?? 0, 'color' => 'amber'],
                            ['label' => 'Antrean Kirim', 'value' => $data['summary']['shipping_pending'] ?? 0, 'color' => 'rose'],
                            ['label' => 'Siap Diambil', 'value' => $data['summary']['ready_for_pickup'] ?? 0, 'color' => 'emerald'],
                        ];
                    @endphp
                    @foreach($metrics as $m)
                        <div class="group/m cursor-default">
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-3 group-hover/m:text-[#4E79A7] transition-colors">{{ $m['label'] }}</p>
                            <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter group-hover/m:scale-110 transition-transform origin-left">{{ number_format($m['value']) }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: Dwell Time & Quick Filter --}}
        <div class="flex flex-col gap-8">
            <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 shadow-sm border border-gray-100 dark:border-slate-800 flex flex-col justify-between group h-1/2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">RATA-RATA WAKTU INAP</p>
                    <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ $data['efficiency']['avg_dwell_hours'] ?? 0 }}<span class="text-sm font-bold text-slate-300 dark:text-slate-700 ml-1 uppercase">Jam</span></h3>
                </div>
                <div class="flex items-center gap-1.5 mt-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#4E79A7]"></span>
                    <p class="text-[9px] font-black text-[#4E79A7] uppercase tracking-widest leading-none">Healthy Flow Status</p>
                </div>
            </div>

            <div class="bg-[#4E79A7] rounded-[40px] p-2 flex flex-col gap-1 shadow-xl shadow-blue-500/10 h-1/2">
                <button class="flex-1 rounded-[32px] text-[10px] font-black uppercase tracking-widest transition-all {{ $startDate == now()->format('Y-m-d') ? 'bg-white text-[#4E79A7]' : 'text-blue-100 hover:bg-white/10' }}"
                        wire:click="$set('startDate', '{{ now()->format('Y-m-d') }}')">Hari Ini</button>
                <button class="flex-1 rounded-[32px] text-[10px] font-black uppercase tracking-widest transition-all {{ $startDate == now()->subDays(7)->format('Y-m-d') ? 'bg-white text-[#4E79A7]' : 'text-blue-100 hover:bg-white/10' }}"
                        wire:click="$set('startDate', '{{ now()->subDays(7)->format('Y-m-d') }}')">7 Hari</button>
                <div class="relative flex-1 flex items-center justify-center gap-2 cursor-pointer group">
                    <input type="date" wire:model.live="startDate" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                    <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-[10px] font-black text-blue-100 uppercase tracking-widest">Kalender</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rack Occupancy Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-[40px] p-10 shadow-sm border border-gray-100 dark:border-slate-800 mb-10">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 mb-12">
            <div>
                <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tighter">Peta Okupansi <span class="text-[#4E79A7]">Rak</span></h2>
                <div class="flex items-center gap-2 mt-2">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Live Tracking Matrix</p>
                </div>
            </div>

            <div class="flex items-center bg-slate-50 dark:bg-slate-800/50 p-1.5 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                @foreach(['FINISH', 'AKSESORIS', 'INBOUND'] as $tab)
                    <button wire:click="setRackTab('{{ $tab }}')" 
                            class="px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $activeRackTab == $tab ? 'bg-white dark:bg-slate-700 text-[#4E79A7] dark:text-blue-400 shadow-md border border-slate-200/50 dark:border-slate-600/50' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#76B7B2]"></div>
                    <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Tersedia</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#EDC948]"></div>
                    <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Optimal</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-900 animate-pulse"></div>
                    <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Penuh</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
            @php
                $categoryMap = [
                    'FINISH' => ['shoes', 'manual', 'manual_l', 'manual_tl', 'manual_tn'],
                    'AKSESORIS' => ['accessories'],
                    'INBOUND' => ['before']
                ];
                $filteredHeatmap = collect($data['storage']['heatmap'] ?? [])
                    ->filter(function($item) use ($categoryMap) {
                        return in_array($item['category'], $categoryMap[$this->activeRackTab] ?? []);
                    });
            @endphp

            @foreach($filteredHeatmap as $rack)
                <div class="group relative p-6 rounded-3xl border transition-all duration-500 flex flex-col items-center justify-center text-center gap-1 overflow-hidden
                    {{ $rack['color'] == 'green' ? 'bg-blue-500/[0.03] border-blue-50 dark:border-blue-500/10 hover:border-blue-500/30' : '' }}
                    {{ $rack['color'] == 'yellow' ? 'bg-amber-500/[0.03] border-amber-50 dark:border-amber-500/10 hover:border-amber-500/30' : '' }}
                    {{ $rack['color'] == 'black' ? 'bg-slate-900 border-slate-800 text-white shadow-xl' : 'text-slate-800 dark:text-white' }}">
                    
                    <div class="absolute inset-0 bg-gradient-to-br transition-opacity duration-500 opacity-0 group-hover:opacity-100 pointer-events-none 
                        {{ $rack['color'] == 'green' ? 'from-[#76B7B2]/10 to-transparent' : '' }}
                        {{ $rack['color'] == 'yellow' ? 'from-[#EDC948]/10 to-transparent' : '' }}
                        {{ $rack['color'] == 'black' ? 'from-slate-800 to-transparent' : '' }}"></div>

                    <p class="relative z-10 text-[11px] font-black uppercase tracking-[0.2em] {{ $rack['color'] == 'black' ? 'text-slate-400' : 'text-slate-800 dark:text-white' }} group-hover:scale-110 transition-transform">{{ $rack['code'] }}</p>
                    <p class="relative z-10 text-[9px] font-bold uppercase tracking-widest {{ $rack['color'] == 'black' ? 'text-blue-400' : 'text-[#4E79A7]' }}">{{ $rack['count'] }} UNIT</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Analysis Row: TABLEAU STYLE CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Tableau Trend Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[40px] p-10 shadow-sm border border-gray-100 dark:border-slate-800"
             wire:ignore
             x-data="tableauTrendChart(@js($data['qc_analytics']['trends']['labels'] ?? []), @js($data['qc_analytics']['trends']['lolos'] ?? []), @js($data['qc_analytics']['trends']['reject'] ?? []))">
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#4E79A7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 3v18h18M7 16l4-4 4 4 5-8" /></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider leading-none">QC Performance Trends</h4>
                        <p class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1.5">Historical Batch Analysis</p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#4E79A7]"></span>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase">QC LOLOS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#E15759]"></span>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase">QC REJECT</span>
                    </div>
                </div>
            </div>
            <div x-ref="chart" class="min-h-[400px]"></div>
        </div>

        {{-- Tableau Donut Chart --}}
        <div class="bg-white dark:bg-slate-900 rounded-[40px] p-10 shadow-sm border border-gray-100 dark:border-slate-800"
             wire:ignore
             x-data="tableauDonutChart(@js($data['qc_analytics']['summary']['lolos'] ?? 0), @js($data['qc_analytics']['summary']['reject'] ?? 0))">
            <div class="mb-10 text-center">
                <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider leading-none">Composition</h4>
                <p class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1.5 italic">QC Success Distribution</p>
            </div>
            <div x-ref="chart" class="min-h-[400px] flex justify-center items-center"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function tableauTrendChart(labels, lolos, reject) {
            return {
                chart: null,
                init() {
                    const options = {
                        series: [
                            { name: 'QC LOLOS', data: lolos || [] },
                            { name: 'QC REJECT', data: reject || [] }
                        ],
                        chart: {
                            type: 'area',
                            height: 400,
                            toolbar: { show: false },
                            fontFamily: 'Inter, sans-serif',
                            background: 'transparent',
                            animations: { enabled: true, easing: 'easeinout', speed: 800 }
                        },
                        colors: ['#4E79A7', '#E15759'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 3 },
                        markers: { 
                            size: 5, 
                            strokeWidth: 2, 
                            hover: { size: 7 },
                            colors: ['#fff'],
                            strokeColors: ['#4E79A7', '#E15759']
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.2,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        grid: {
                            borderColor: document.documentElement.classList.contains('dark') ? '#1e293b' : '#f1f5f9',
                            strokeDashArray: 4,
                            yaxis: { lines: { show: true } },
                            xaxis: { lines: { show: false } }
                        },
                        xaxis: {
                            categories: labels || [],
                            labels: { 
                                style: { 
                                    colors: '#94a3b8', 
                                    fontSize: '10px',
                                    fontWeight: 600
                                } 
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: { 
                                style: { 
                                    colors: '#94a3b8',
                                    fontSize: '10px',
                                    fontWeight: 600
                                } 
                            }
                        },
                        legend: { show: false },
                        tooltip: {
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            shared: true,
                            intersect: false,
                            y: { formatter: (val) => val + ' Units' }
                        }
                    };
                    this.chart = new ApexCharts(this.$refs.chart, options);
                    this.chart.render();
                    window.trendChart = this;
                },
                update(data) {
                    if (this.chart) {
                        this.chart.updateOptions({
                            grid: { borderColor: document.documentElement.classList.contains('dark') ? '#1e293b' : '#f1f5f9' },
                            xaxis: { categories: data.labels || [] }
                        });
                        this.chart.updateSeries([
                            { name: 'QC LOLOS', data: data.lolos || [] },
                            { name: 'QC REJECT', data: data.reject || [] }
                        ]);
                    }
                }
            }
        }

        function tableauDonutChart(lolos, reject) {
            return {
                chart: null,
                init() {
                    let lVal = parseFloat(lolos) || 0;
                    let rVal = parseFloat(reject) || 0;
                    
                    const options = {
                        series: [lVal, rVal],
                        chart: {
                            type: 'donut',
                            height: 400,
                            fontFamily: 'Inter, sans-serif',
                            animations: { enabled: true }
                        },
                        colors: ['#4E79A7', '#E15759'],
                        labels: ['QC LOLOS', 'QC REJECT'],
                        dataLabels: { enabled: false },
                        stroke: { width: 0 },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '80%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'TOTAL QC',
                                            fontSize: '10px',
                                            fontWeight: 900,
                                            color: '#94a3b8',
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        },
                                        value: {
                                            fontSize: '32px',
                                            fontWeight: 900,
                                            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e293b',
                                            formatter: (val) => val
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: '10px',
                            fontWeight: 700,
                            markers: { radius: 2 },
                            labels: { colors: '#94a3b8' }
                        }
                    };
                    this.chart = new ApexCharts(this.$refs.chart, options);
                    this.chart.render();
                    window.donutChart = this;
                },
                update(l, r) {
                    if (this.chart) {
                        this.chart.updateOptions({
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            value: { color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e293b' }
                                        }
                                    }
                                }
                            }
                        });
                        this.chart.updateSeries([parseFloat(l) || 0, parseFloat(r) || 0]);
                    }
                }
            }
        }

        window.addEventListener('dataUpdated', event => {
            const data = (event.detail && event.detail.qc_analytics) ? event.detail : (event.detail[0] || {});
            if (data.qc_analytics) {
                // Determine summary counts (fallback to trends sum if needed)
                let lolosCount = parseFloat(data.qc_analytics.summary?.lolos);
                let rejectCount = parseFloat(data.qc_analytics.summary?.reject);
                
                if (isNaN(lolosCount) || isNaN(rejectCount) || (lolosCount === 0 && rejectCount === 0)) {
                    lolosCount = (data.qc_analytics.trends?.lolos || []).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                    rejectCount = (data.qc_analytics.trends?.reject || []).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                }

                if (window.trendChart) window.trendChart.update(data.qc_analytics.trends);
                if (window.donutChart) window.donutChart.update(lolosCount, rejectCount);
            }
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        .font-sans { font-family: 'Inter', sans-serif !important; }
        input[type="date"]::-webkit-calendar-picker-indicator { 
            background: transparent; bottom: 0; color: transparent; cursor: pointer; height: auto; left: 0; position: absolute; right: 0; top: 0; width: auto; 
        }
    </style>
</div>
