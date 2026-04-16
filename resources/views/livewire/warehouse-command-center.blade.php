<div class="p-6 space-y-8 bg-slate-950 min-h-screen text-slate-200" x-data="{ 
    syncing: @entangle('isLoading')
}">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h2 class="text-2xl font-black tracking-tight uppercase text-white">Pusat Komando Operasional</h2>
            </div>
            <p class="text-slate-400 text-sm font-medium tracking-wide uppercase">Warehouse Division • Real-time Monitoring</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Last Sync</p>
                <p class="text-emerald-400 font-mono font-bold">{{ $lastSync ?? '--:--:--' }}</p>
            </div>
            <button wire:click="syncNow" wire:loading.attr="disabled"
                class="relative group px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold transition-all duration-300 shadow-lg shadow-emerald-900/20 overflow-hidden">
                <div class="flex items-center gap-2">
                    <svg wire:loading.remove wire:target="syncNow" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg wire:loading wire:target="syncNow" class="w-5 h-5 animate-spin pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="syncNow">TARIK DATA LIVE</span>
                    <span wire:loading wire:target="syncNow">MENARIK DATA...</span>
                </div>
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- SPK Pending --}}
        <div class="bg-slate-900/50 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">SPK Pending</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-white">{{ number_format($summary['pending_reception'] ?? 0) }}</h3>
                <span class="text-amber-400 text-xs font-bold uppercase">Antrean</span>
            </div>
            <div class="mt-4 w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: 45%"></div>
            </div>
        </div>

        {{-- Di Finish --}}
        <div class="bg-slate-900/50 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Di Finish</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-white">{{ number_format($summary['finished_not_stored'] ?? 0) }}</h3>
                <span class="text-blue-400 text-xs font-bold uppercase">Selesai</span>
            </div>
            <div class="mt-4 w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: 65%"></div>
            </div>
        </div>

        {{-- Antrean Kirim --}}
        <div class="bg-slate-900/50 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Antrean Kirim</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-white">{{ number_format($summary['shipping_pending'] ?? 0) }}</h3>
                <span class="text-indigo-400 text-xs font-bold uppercase">Paket</span>
            </div>
            <div class="mt-4 w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full" style="width: 25%"></div>
            </div>
        </div>

        {{-- Siap Diambil --}}
        <div class="bg-slate-900/50 border border-slate-800 p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Siap Diambil</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-black text-white">{{ number_format($summary['ready_for_pickup'] ?? 0) }}</h3>
                <span class="text-emerald-400 text-xs font-bold uppercase">Offline</span>
            </div>
            <div class="mt-4 w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: 80%"></div>
            </div>
        </div>
    </div>

    {{-- Module Status Section --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800/50 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Inventori</h4>
                    <p class="text-sm font-bold text-white">{{ number_format($inventoryCount) }} <span class="text-[10px] text-slate-500 font-medium">Items</span></p>
                </div>
            </div>
            <div class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest">Live Sync</span>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800/50 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Permintaan Material</h4>
                    <p class="text-sm font-bold text-white">{{ number_format($requestCount) }} <span class="text-[10px] text-slate-500 font-medium">Items</span></p>
                </div>
            </div>
            <div class="px-2.5 py-1 rounded-lg {{ $requestCount > 0 ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-slate-800 border border-slate-700' }}">
                <span class="text-[9px] font-black {{ $requestCount > 0 ? 'text-emerald-400' : 'text-slate-500' }} uppercase tracking-widest">{{ $requestCount > 0 ? 'Live Sync' : 'Waiting Data' }}</span>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800/50 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Riwayat Transaksi</h4>
                    <p class="text-sm font-bold text-white">{{ number_format($transactionCount) }} <span class="text-[10px] text-slate-500 font-medium">Items</span></p>
                </div>
            </div>
            <div class="px-2.5 py-1 rounded-lg {{ $transactionCount > 0 ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-slate-800 border border-slate-700' }}">
                <span class="text-[9px] font-black {{ $transactionCount > 0 ? 'text-emerald-400' : 'text-slate-500' }} uppercase tracking-widest">{{ $transactionCount > 0 ? 'Live Sync' : 'Waiting Data' }}</span>
            </div>
        </div>
    </div>

    {{-- QC Performance Section --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3 bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white uppercase tracking-tight">QC Performance Trends</h3>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Historical Batch Analysis</p>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-widest">
                    <div class="flex items-center gap-1.5 text-indigo-400">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div> QC Lolos
                    </div>
                    <div class="flex items-center gap-1.5 text-rose-400">
                        <div class="w-2 h-2 rounded-full bg-rose-500"></div> QC Reject
                    </div>
                </div>
            </div>
            <div class="h-[300px] relative">
                <canvas id="qcTrendsChart"></canvas>
            </div>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
            <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-6 w-full text-left">Composition</h3>
            <div class="relative w-full aspect-square max-w-[200px]">
                <canvas id="qcCompositionChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-[10px] text-slate-500 uppercase font-black">Total QC</p>
                    <p class="text-3xl font-black text-white">{{ number_format(($qcAnalytics['stats']['data'][0] ?? 0) + ($qcAnalytics['stats']['data'][1] ?? 0)) }}</p>
                </div>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-4 w-full">
                <div class="text-left">
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">QC Lolos</span>
                    </div>
                    <p class="text-lg font-black text-white">{{ number_format($qcAnalytics['stats']['data'][0] ?? 0) }}</p>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-1.5 mb-1 justify-end">
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">QC Reject</span>
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                    </div>
                    <p class="text-lg font-black text-white">{{ number_format($qcAnalytics['stats']['data'][1] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content: Rack Map --}}
    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-800/50">
            <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Peta Okupansi Rak
                </h3>
            </div>
            <div class="flex gap-6 text-[10px] font-bold uppercase tracking-[0.2em]">
                <div class="flex items-center gap-2 text-emerald-400">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]"></div> Low
                </div>
                <div class="flex items-center gap-2 text-amber-400">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.3)]"></div> High
                </div>
                <div class="flex items-center gap-2 text-rose-400">
                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.3)]"></div> Full
                </div>
            </div>
        </div>

        {{-- Grouped Racks (3 Categories) --}}
        @php
            $racks = collect($rackMap);
            $categories = [
                'Inbound' => $racks->filter(fn($r) => in_array($r['category'] ?? '', ['manual', 'before', 'manual_l', 'manual_tl', 'manual_tn'])),
                'Aksesoris' => $racks->filter(fn($r) => ($r['category'] ?? '') === 'accessories'),
                'Rak Finish' => $racks->filter(fn($r) => ($r['category'] ?? '') === 'shoes'),
            ];
        @endphp

        <div class="grid grid-cols-1 gap-8">
            @foreach ($categories as $title => $items)
                @if($items->isNotEmpty())
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <h4 class="text-sm font-black text-slate-300 uppercase tracking-[0.2em]">{{ $title }}</h4>
                            <div class="h-[1px] flex-1 bg-gradient-to-r from-slate-700 to-transparent"></div>
                            <span class="text-[10px] font-black text-slate-500 uppercase">{{ $items->count() }} Rak</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                            @foreach ($items as $rack)
                                @php
                                    $colorClass = match($rack['color']) {
                                        'yellow' => 'from-amber-500/10 to-transparent border-amber-500/30 text-amber-200',
                                        'black', 'rose' => 'from-rose-500/20 to-transparent border-rose-500/40 text-rose-100',
                                        default => 'from-emerald-500/10 to-transparent border-emerald-500/20 text-emerald-200'
                                    };
                                    $bulletColor = match($rack['color']) {
                                        'yellow' => 'bg-amber-500',
                                        'black', 'rose' => 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.3)]',
                                        default => 'bg-emerald-500'
                                    };
                                @endphp
                                <div class="bg-gradient-to-br {{ $colorClass }} border p-3 rounded-xl flex flex-col items-center justify-center text-center transition-all duration-300 hover:scale-105 hover:shadow-xl hover:border-white/20 group relative overflow-hidden">
                                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <span class="text-[10px] font-black tracking-widest uppercase mb-1 relative z-10">{{ $rack['code'] }}</span>
                                    <div class="flex items-center gap-1.5 relative z-10">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $bulletColor }}"></div>
                                        <span class="text-xs font-mono font-bold">{{ $rack['usage'] }}%</span>
                                    </div>
                                    <span class="text-[9px] text-slate-500 mt-1 relative z-10">{{ $rack['count'] }}/{{ $rack['capacity'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            initCharts();
        });

        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
        });

        // Handle Chart Re-init after Sync
        window.addEventListener('swal', () => {
            setTimeout(initCharts, 500);
        });

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
                                borderColor: '#818cf8', // indigo-400
                                backgroundColor: 'rgba(129, 140, 248, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#818cf8'
                            },
                            {
                                label: 'QC Reject',
                                data: qcData.trends.reject,
                                borderColor: '#fb7185', // rose-400
                                backgroundColor: 'rgba(251, 113, 133, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#fb7185'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(51, 65, 85, 0.5)', drawBorder: false },
                                ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
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
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '80%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }
    </script>
</div>
