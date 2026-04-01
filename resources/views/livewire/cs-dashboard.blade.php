<div class="px-6 py-6 pb-20">
    {{-- Header & Universal Filter Bar (Moved out of slot for stability) --}}
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-slate-200/40 dark:shadow-none">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-3xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/40 group">
                <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">Unified Analytics</h1>
                    <span class="px-2 py-1 rounded-lg bg-emerald-500 text-white text-[10px] font-black tracking-[0.2em] animate-pulse">LIVE</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                    <span class="w-2h-2 rounded-full bg-slate-300"></span>
                    Terminal Sync: {{ $lastSynced->format('H:i:s') }} • Sleekflow + Master API
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            {{-- Robust Filter Control --}}
            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-[32px] border border-gray-100 dark:border-gray-800">
                <div class="flex items-center px-4 gap-3">
                    <input type="date" 
                        wire:model.live.debounce.500ms="startDate"
                        wire:key="sd-{{ time() }}"
                        class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                    <span class="text-slate-300 dark:text-slate-700 font-black">—</span>
                    <input type="date" 
                        wire:model.live.debounce.500ms="endDate"
                        wire:key="ed-{{ time() }}"
                        class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                </div>
                
                <button type="button"
                    wire:click.prevent="applyFilter" 
                    wire:loading.attr="disabled"
                    class="px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3 shadow-lg shadow-indigo-500/25">
                    <span wire:loading.remove wire:target="applyFilter">TERAPKAN</span>
                    <span wire:loading wire:target="applyFilter" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        MEMUAT...
                    </span>
                </button>
            </div>

            {{-- Force Sync Button --}}
            <button type="button"
                wire:click.prevent="refreshManually" 
                class="w-14 h-14 rounded-[24px] bg-white dark:bg-slate-800 border border-gray-100 dark:border-gray-700 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm flex items-center justify-center group">
                <svg class="w-6 h-6 group-hover:rotate-180 transition-transform duration-700 {{ $isLoading ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
        </div>
    </div>

    @if($errorMessage)
        <div class="mb-10 p-6 rounded-[32px] bg-rose-500/10 border border-rose-500/20 flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-rose-500 flex items-center justify-center text-white shrink-0"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            <p class="text-sm font-black text-rose-600 uppercase tracking-widest">{{ $errorMessage }}</p>
        </div>
    @endif

    {{-- SECTION: CHAT ANALYTICS --}}
    <div class="mb-20">
        <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] mb-8 px-2 flex items-center gap-4">
            <span class="h-1 w-12 bg-indigo-500 rounded-full"></span>
            Chat Metrics (Sleekflow)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6 mb-10" wire:poll.30s="autoRefresh">
            {{-- Total Contacts --}}
            <div class="bg-indigo-600 rounded-[40px] p-8 shadow-2xl shadow-indigo-500/30 text-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-100/60 mb-3 leading-none">Total Contacts</p>
                <h3 class="text-4xl font-black tracking-tighter leading-none">{{ number_format($sleekflowMetrics['totalContacts'] ?? 0) }}</h3>
            </div>

            @php
                $chatItems = [
                    ['label' => 'Greeting', 'val' => $sleekflowMetrics['totalGreeting'] ?? 0, 'col' => 'indigo'],
                    ['label' => 'Konsultasi', 'val' => $sleekflowMetrics['totalKonsul'] ?? 0, 'col' => 'blue'],
                    ['label' => 'Closing', 'val' => $sleekflowMetrics['totalClosing'] ?? 0, 'col' => 'emerald'],
                ];
            @endphp
            @foreach($chatItems as $item)
                <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 leading-none">{{ $item['label'] }}</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">{{ number_format($item['val']) }}</h3>
                </div>
            @endforeach

            {{-- Unhandled --}}
            <div class="md:col-span-2 bg-rose-600 rounded-[40px] p-8 shadow-2xl shadow-rose-500/30 text-white flex items-center justify-between group overflow-hidden">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-widest text-rose-100/60 mb-3 leading-none uppercase">Unhandled Chat</p>
                    <h3 class="text-4xl font-black tracking-tighter leading-none">{{ number_format($sleekflowMetrics['unhandledCount'] ?? 0) }}</h3>
                    <p class="text-[10px] font-black text-rose-100/40 uppercase tracking-widest mt-4">Rate: {{ $sleekflowMetrics['unhandledRate'] ?? 0 }}% Response Lag</p>
                </div>
                <div class="w-16 h-16 rounded-3xl bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>

        {{-- Chat Leaderboard --}}
        <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">
                        <th class="px-10 py-6">Customer Service Agent</th>
                        <th class="px-6 py-6">Greeting</th>
                        <th class="px-6 py-6">Konsultasi</th>
                        <th class="px-6 py-6 text-rose-500">Unhandle</th>
                        <th class="px-6 py-6 text-center">Persentase</th>
                        <th class="px-10 py-6 text-center">Closing</th>
                    </tr>
                </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($sleekflowMetrics['ownerStats'] ?? [] as $stat)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-500 uppercase">{{ substr($stat['display_name'] ?: '?', 0, 1) }}</div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ $stat['display_name'] }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 font-mono italic">AGENT • {{ $stat['total_contacts'] ?? 0 }} TOTAL</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-sm font-black text-slate-600 dark:text-slate-400">{{ number_format($stat['total_greeting'] ?? 0) }}</td>
                                <td class="px-6 py-6 text-sm font-black text-slate-600 dark:text-slate-400">{{ number_format($stat['total_konsul'] ?? 0) }}</td>
                                <td class="px-6 py-6 text-sm font-black text-rose-500 bg-rose-500/5">{{ number_format($stat['total_unhandled'] ?? 0) }}</td>
                                <td class="px-6 py-6 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-black text-emerald-500 tracking-tighter">{{ $stat['consultation_rate'] ?? 0 }}%</span>
                                        <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest mt-0.5">Rate Efficiency</span>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <span class="px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-black shadow-sm group-hover:shadow-md transition-shadow">{{ number_format($stat['total_closing'] ?? 0) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-20 text-center text-slate-300 font-black uppercase tracking-widest text-xs">No active chat sessions</td></tr>
                        @endforelse
                    </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION: OPERATIONAL ANALYTICS --}}
    <div>
        <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] mb-8 px-2 flex items-center gap-4">
            <span class="h-1 w-12 bg-emerald-500 rounded-full"></span>
            Operational Performance (API)
        </h2>

        <div class="grid grid-cols-1 gap-8 items-start mb-20">
            <div class="flex flex-col gap-8">
                {{-- Master Sales Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Revenue Card --}}
                    <div class="bg-emerald-600 rounded-[40px] p-8 shadow-2xl shadow-emerald-500/30 text-white relative overflow-hidden group lg:col-span-2">
                        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-widest text-emerald-100/60 mb-4 leading-none font-bold">Consolidated Revenue</p>
                                <h3 class="text-5xl font-black tracking-tighter leading-none">Rp {{ number_format($apiSummary['revenue'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="bg-white/20 p-4 rounded-3xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Avg Deal Value --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-center">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 leading-none">Rata-rata Penjualan (Avg Deal)</p>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">Rp {{ number_format($apiSummary['avg_deal'] ?? 0, 0, ',', '.') }}</h3>
                    </div>

                    {{-- Sepatu Masuk --}}
                    <div class="bg-indigo-600 rounded-[40px] p-8 shadow-2xl shadow-indigo-500/30 text-white relative overflow-hidden group">
                        <p class="text-[11px] font-black uppercase tracking-widest text-indigo-100/60 mb-4 leading-none font-bold uppercase">Sepatu Masuk</p>
                        <h3 class="text-4xl font-black tracking-tighter leading-none">{{ number_format($apiSummary['total_sepatu_masuk'] ?? 0) }}</h3>
                        <p class="text-[10px] font-bold text-indigo-100/40 uppercase tracking-widest mt-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                            Units Registered
                        </p>
                    </div>

                    {{-- in Gudang --}}
                    <div class="bg-slate-800 rounded-[40px] p-8 shadow-2xl shadow-slate-900/30 text-white relative overflow-hidden group">
                        <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-4 leading-none font-bold uppercase">Diterima Gudang (In-Bound)</p>
                        <h3 class="text-4xl font-black tracking-tighter leading-none">{{ number_format($apiSummary['in_gudang'] ?? 0) }}</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-4">Verified by Warehouse</p>
                    </div>

                    {{-- Closing Statistics --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-center">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 leading-none">Total Closing</p>
                                <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">{{ number_format($apiSummary['total_closing'] ?? 0) }}</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-2 leading-none">Closing Rate</p>
                                <span class="text-lg font-black text-emerald-500">{{ $apiSummary['kalkulasi_closing'] ?? '0%' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual Chart --}}
                <div class="bg-white dark:bg-slate-900 rounded-[40px] p-10 border border-gray-100 dark:border-gray-800 shadow-sm"
                    wire:key="chart-{{ $startDate }}-{{ $endDate }}"
                    x-data="chartComponent(@js(collect($perCs)->pluck('cs_name')->toArray()), @js(collect($perCs)->pluck('revenue')->toArray()))"
                    wire:ignore>
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest mb-10 leading-none">Revenue Breakdown per Agent</h4>
                    <div x-ref="salesChart" class="min-h-[400px]"></div>
                </div>
            </div>
        </div>

        {{-- NEW SECTION: DETAILED AGENT PERFORMANCE --}}
        <div class="mb-20">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] mb-8 px-2 flex items-center gap-4">
                <span class="h-1 w-12 bg-emerald-500 rounded-full"></span>
                Ranking Performa Individu CS
            </h2>

            <div class="bg-[#1a1c23] rounded-[40px] border border-gray-800 shadow-2xl overflow-hidden overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="bg-[#242731] text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-gray-800">
                            <th class="px-8 py-6">CS Name</th>
                            <th class="px-4 py-6 text-center">Closing</th>
                            <th class="px-4 py-6 text-center">In Gudang</th>
                            <th class="px-4 py-6 text-center">Items In</th>
                            <th class="px-4 py-6 text-center">Langsung</th>
                            <th class="px-6 py-6 text-right">Revenue</th>
                            <th class="px-8 py-6 text-right">Avg Deal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($perCs as $idx => $cs)
                            <tr class="hover:bg-slate-800/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full {{ $idx < 3 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500 border border-white/5' }} flex items-center justify-center font-black text-xs">
                                            {{ substr($cs['cs_name'] ?? '?', 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-white group-hover:text-emerald-400 transition-colors">{{ $cs['cs_name'] ?? 'Agent' }}</span>
                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">RANK #{{ $idx + 1 }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6 text-center text-sm font-black text-white">{{ number_format($cs['total_closing'] ?? 0) }}</td>
                                <td class="px-4 py-6 text-center text-sm font-black text-emerald-500/80">{{ number_format($cs['in_gudang'] ?? 0) }}</td>
                                <td class="px-4 py-6 text-center text-sm font-black text-indigo-400">{{ number_format($cs['total_sepatu_masuk'] ?? 0) }}</td>
                                <td class="px-4 py-6 text-center text-sm font-black text-emerald-400/60">{{ number_format($cs['langsung'] ?? 0) }}</td>
                                <td class="px-6 py-6 text-right text-sm font-black text-white">Rp{{ number_format($cs['revenue'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-8 py-6 text-right text-sm font-black text-slate-400">Rp{{ number_format($cs['avg_deal'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function chartComponent(categories, data) {
            return {
                chart: null,
                init() {
                    if (this.chart) this.chart.destroy();
                    const options = {
                        series: [{ name: "Revenue", data: data }],
                        chart: { 
                            type: "bar", 
                            height: 400, 
                            toolbar: { show: false },
                            fontFamily: 'Inter, sans-serif'
                        },
                        plotOptions: { 
                            bar: { 
                                columnWidth: "22%", 
                                borderRadius: 12, 
                                borderRadiusApplication: 'end',
                                distributed: true,
                                dataLabels: { position: 'top' }
                            } 
                        },
                        xaxis: { 
                            categories: categories, 
                            labels: { 
                                style: { colors: "#94a3b8", fontWeight: 700, fontSize: "11px" } 
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: { 
                            labels: { 
                                style: { colors: "#94a3b8", fontWeight: 700, fontSize: "11px" },
                                formatter: (val) => {
                                    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                    if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                                    return val;
                                }
                            } 
                        },
                        fill: { 
                            type: "gradient", 
                            gradient: { 
                                shade: "dark", 
                                type: "vertical", 
                                shadeIntensity: 0.1, 
                                gradientToColors: ["#10b981", "#3b82f6", "#6366f1"], 
                                stops: [0, 100] 
                            } 
                        },
                        grid: { 
                            borderColor: "#334155", 
                            strokeDashArray: 8,
                            padding: { top: 20 }
                        },
                        dataLabels: { 
                            enabled: true,
                            offsetY: -25,
                            style: { fontSize: '11px', colors: ["#f8fafc"], fontWeight: 900 },
                            formatter: (val) => {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                                return val;
                            }
                        },
                        colors: ["#6366f1", "#4f46e5", "#4338ca", "#3730a3", "#312e81"],
                        legend: { show: false },
                        tooltip: { theme: 'dark' }
                    };
                    this.chart = new ApexCharts(this.$refs.salesChart, options);
                    this.chart.render();
                }
            };
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
        input[type="date"]::-webkit-calendar-picker-indicator { 
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }
        input[type="date"] { position: relative; }
    </style>
</div>
