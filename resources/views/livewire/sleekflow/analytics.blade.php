<div class="min-h-screen p-4 md:p-6 lg:p-8 space-y-6">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="text-[10px] font-bold tracking-[0.2em] text-emerald-400 uppercase mb-1">Sistem Internal</div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Sleekflow Analytics</h1>
        </div>
        
        <div class="w-full md:w-auto">
            <div class="bg-[#1e2336] p-2 rounded-2xl flex flex-col md:flex-row items-center gap-4 shadow-xl border border-white/5">
                <div class="flex items-center gap-3 w-full md:w-auto px-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest whitespace-nowrap">Pilih Periode:</span>
                    <input type="month" wire:model.live="month" 
                        class="bg-[#151928] border-none text-white text-sm rounded-xl px-4 py-2 w-full focus:ring-2 focus:ring-emerald-500/50">
                </div>
                <button wire:click="$refresh" class="w-full md:w-auto px-6 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl transition-all uppercase tracking-wider whitespace-nowrap flex items-center justify-center gap-2">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Segarkan Data
                </button>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        {{-- Card 1 --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Kontak</span>
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-white">{{ number_format($summary['total_contacts'], 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Card 2 --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Prospek Baru</span>
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-white">{{ number_format($summary['total_enquiries'], 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Card 3 --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pesan Terkirim</span>
                <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-white">{{ number_format($summary['total_messages_sent'], 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Card 4 --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-pink-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pesan Diterima</span>
                <div class="w-8 h-8 rounded-full bg-pink-500/20 flex items-center justify-center text-pink-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-white">{{ number_format($summary['total_messages_received'], 0, ',', '.') }}</h3>
            </div>
        </div>

        {{-- Card 5 (First Response Time) --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">First Response</span>
                <div class="w-8 h-8 rounded-full bg-rose-500/20 flex items-center justify-center text-rose-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl md:text-3xl font-black text-white">{{ $summary['avg_first_response_time'] }}</h3>
            </div>
        </div>

        {{-- Card 6 (All Response Time) --}}
        <div class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center justify-between relative z-10 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg Response</span>
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl md:text-3xl font-black text-white">{{ $summary['avg_response_time'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Combo Chart Section --}}
    <div class="bg-[#1e2336] rounded-2xl border border-white/5 p-6" wire:ignore>
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Korelasi Leads Masuk vs Kecepatan Respon CS</h2>
        </div>
        <div id="sleekflowChart" class="w-full h-[350px]"></div>
    </div>

    {{-- Script for Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let chart;
            
            const renderChart = () => {
                const dates = @json($chartDates);
                const enquiries = @json($chartEnquiries);
                const responseTimes = @json($chartResponseTimes);

                const options = {
                    series: [{
                        name: 'Chat Masuk',
                        type: 'column',
                        data: enquiries
                    }, {
                        name: 'First Response Time',
                        type: 'line',
                        data: responseTimes
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        toolbar: { show: false },
                        background: 'transparent',
                        fontFamily: 'inherit'
                    },
                    stroke: {
                        width: [0, 4],
                        curve: 'smooth'
                    },
                    colors: ['#10b981', '#f43f5e'], // Emerald for Bar, Rose for Line
                    fill: {
                        type: ['solid', 'solid'],
                        opacity: [0.15, 1],
                    },
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [1],
                        formatter: function (val) {
                            // Convert back minutes to MM:SS format for data labels
                            if (val === 0) return '';
                            let m = Math.floor(val);
                            let s = Math.round((val - m) * 60);
                            return m + "m " + s + "s";
                        },
                        style: {
                            colors: ['#fff']
                        }
                    },
                    labels: dates,
                    xaxis: {
                        type: 'category',
                        labels: { style: { colors: '#94a3b8' } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: [{
                        title: { text: 'Total Chat Masuk', style: { color: '#10b981' } },
                        labels: { style: { colors: '#94a3b8' } },
                        min: 0
                    }, {
                        opposite: true,
                        title: { text: 'First Response (Menit)', style: { color: '#f43f5e' } },
                        labels: { style: { colors: '#94a3b8' } },
                        min: 0
                    }],
                    grid: {
                        borderColor: 'rgba(255,255,255,0.05)',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function (val, { seriesIndex }) {
                                if (seriesIndex === 0) return val + " Chat";
                                // Line chart (Minutes) -> format to mm:ss
                                let m = Math.floor(val);
                                let s = Math.round((val - m) * 60);
                                return m + " menit " + s + " detik";
                            }
                        }
                    },
                    legend: {
                        labels: { colors: '#cbd5e1' },
                        position: 'top'
                    }
                };

                if (chart) {
                    chart.destroy();
                }
                
                chart = new ApexCharts(document.querySelector("#sleekflowChart"), options);
                chart.render();
            };

            // Render first time
            renderChart();

            // Re-render when Livewire updates the month
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    setTimeout(() => {
                        renderChart();
                    }, 50);
                });
            });
        });
    </script>

    {{-- Data Table --}}
    <div class="bg-[#1e2336] rounded-2xl border border-white/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">Laporan Harian Sleekflow</h2>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#151928] text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-4 border-b border-white/5">Tanggal</th>
                        <th class="px-6 py-4 border-b border-white/5 text-center">Prospek Baru</th>
                        <th class="px-6 py-4 border-b border-white/5 text-center">Active Convs</th>
                        <th class="px-6 py-4 border-b border-white/5 text-center">Pesan Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($analytics as $item)
                        <tr wire:key="sf-{{ $item->id }}" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($item->date_time)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($item->date_time)->translatedFormat('l') }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-md bg-emerald-500/10 text-emerald-400 text-xs font-bold">
                                    {{ number_format($item->number_of_new_enquires, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-semibold text-slate-300">{{ number_format($item->number_of_active_conversations, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-semibold text-pink-400">{{ number_format($item->number_of_message_received, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="text-sm">Belum ada data untuk periode ini.</p>
                                <p class="text-[10px] uppercase tracking-widest mt-1">Silakan sinkronisasi atau ubah filter bulan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($analytics->hasPages())
        <div class="px-6 py-4 border-t border-white/5 bg-[#151928]">
            {{ $analytics->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
    </div>
</div>
