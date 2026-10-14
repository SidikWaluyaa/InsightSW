<div class="space-y-6">

    <x-ui.page-header title="Analitik Sleekflow" subtitle="Prospek baru, volume pesan, dan kecepatan balasan CS per hari.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" refresh="syncData" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate">
        <span class="text-slate-300 dark:text-gray-600">•</span>
        <span>{{ $daysWithData }} dari {{ $rangeDays }} hari memiliki data</span>
        @if($syncMessage)
            <span class="font-semibold {{ $syncOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $syncMessage }}</span>
        @endif
    </x-ui.range-info>

    {{-- Modal rumus (dibuka tombol "i" di kartu, hanya Admin); di luar blok yang di-render ulang agar Alpine tidak rusak --}}
    <div wire:ignore><x-ui.formula-modal /></div>

    {{-- KPI Cards --}}
    @php
        // Kelas ditulis utuh agar ikut ter-generate Tailwind
        $tones = [
            'blue' => ['glow' => 'bg-blue-500/10', 'icon' => 'bg-blue-500/20 text-blue-400'],
            'emerald' => ['glow' => 'bg-emerald-500/10', 'icon' => 'bg-emerald-500/20 text-emerald-400'],
            'purple' => ['glow' => 'bg-purple-500/10', 'icon' => 'bg-purple-500/20 text-purple-400'],
            'pink' => ['glow' => 'bg-pink-500/10', 'icon' => 'bg-pink-500/20 text-pink-400'],
            'rose' => ['glow' => 'bg-rose-500/10', 'icon' => 'bg-rose-500/20 text-rose-400'],
            'amber' => ['glow' => 'bg-amber-500/10', 'icon' => 'bg-amber-500/20 text-amber-400'],
        ];
        $icons = [
            'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'user-plus' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'send' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
            'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ];
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach($cards as $key => $card)
            @php $tone = $tones[$card['tone']]; @endphp
            <div wire:key="sf-card-{{ $key }}" class="bg-[#1e2336] rounded-2xl p-5 border border-white/5 relative overflow-hidden group flex flex-col">
                <div class="absolute top-0 right-0 w-24 h-24 {{ $tone['glow'] }} rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between gap-2 relative z-10 mb-4">
                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">
                        <span>{{ $card['label'] }}</span>
                        @if($card['info'])
                            <button type="button" wire:click="showFormula('{{ $key }}')" wire:loading.attr="disabled" wire:target="showFormula('{{ $key }}')"
                                title="Info rumus" aria-label="Info rumus {{ $card['label'] }}"
                                class="shrink-0 inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-slate-600 text-[8px] font-black normal-case tracking-normal leading-none text-slate-400 hover:border-emerald-400 hover:text-emerald-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-wait">i</button>
                        @endif
                    </span>
                    <div class="shrink-0 w-8 h-8 rounded-full {{ $tone['icon'] }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$card['icon']] }}" /></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    @if($card['sub'] !== null)
                        <h3 class="text-2xl font-black text-white whitespace-nowrap">{{ $card['value'] }}</h3>
                        <p class="text-[10px] text-slate-400 mt-1">{{ $card['sub'] }}</p>
                    @else
                        <h3 class="text-3xl font-black text-white">{{ $card['value'] }}</h3>
                    @endif
                </div>
                <p class="relative z-10 mt-3 pt-3 border-t border-white/5 text-[11px] leading-snug text-slate-400">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Combo Chart Section --}}
    <div class="bg-[#1e2336] rounded-2xl border border-white/5 p-6" wire:ignore>
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Perbandingan Prospek Baru dan Kecepatan Balasan CS</h2>
        </div>
        <div id="sleekflowChart" class="w-full h-[350px]"></div>
    </div>

    {{-- Script for Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let chart;
            
            const renderChart = ({ dates, enquiries, responseTimes }) => {
                // Rentang panjang: sembunyikan label titik & kurangi tick agar grafik tetap terbaca
                const dense = dates.length > 31;

                const options = {
                    series: [{
                        name: 'Prospek Baru',
                        type: 'column',
                        data: enquiries
                    }, {
                        name: 'Waktu Balas Pertama',
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
                        width: [0, dense ? 2 : 4],
                        curve: 'smooth'
                    },
                    colors: ['#10b981', '#f43f5e'], // Emerald for Bar, Rose for Line
                    fill: {
                        type: ['solid', 'solid'],
                        opacity: [0.15, 1],
                    },
                    dataLabels: {
                        enabled: !dense,
                        enabledOnSeries: [1],
                        formatter: function (val) {
                            // Convert back minutes to MM:SS format for data labels
                            if (!val) return '';
                            let m = Math.floor(val);
                            let s = Math.round((val - m) * 60);
                            return m + " mnt";
                        },
                        style: {
                            colors: ['#f43f5e']
                        },
                        background: {
                            enabled: true,
                            foreColor: '#fff',
                            borderWidth: 0
                        }
                    },
                    labels: dates,
                    xaxis: {
                        type: 'category',
                        tickAmount: dense ? 12 : undefined,
                        labels: { style: { colors: '#94a3b8' }, rotate: 0, hideOverlappingLabels: true },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: [{
                        title: { text: 'Jumlah Prospek Baru', style: { color: '#10b981' } },
                        labels: { style: { colors: '#94a3b8' }, formatter: (val) => Math.round(val) },
                        min: 0
                    }, {
                        opposite: true,
                        title: { text: 'Waktu Balas Pertama (menit)', style: { color: '#f43f5e' } },
                        labels: { style: { colors: '#94a3b8' }, formatter: (val) => Math.round(val) },
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
                                if (seriesIndex === 0) return val + " prospek";
                                if (val === null || val === undefined) return "Tidak ada balasan staf";
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
            renderChart(@json($chart));

            // Re-render saat rentang tanggal berubah / data disinkronkan
            Livewire.on('sleekflow-chart-update', ({ chart: data }) => renderChart(data));
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
                        <th class="px-6 py-4 border-b border-white/5 text-center">Percakapan Aktif</th>
                        <th class="px-6 py-4 border-b border-white/5 text-center">Pesan Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($analytics as $item)
                        <tr wire:key="sf-{{ $item->id }}" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($item->date_time)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ \Carbon\Carbon::parse($item->date_time)->translatedFormat('l') }}
                                    @if(\App\Livewire\Sleekflow\Analytics::isOffDay($item))
                                        <span class="ml-1 px-1.5 py-px rounded bg-slate-500/20 text-slate-300 font-bold uppercase tracking-wider" title="Tidak ada balasan dari staf: tidak dihitung di rata-rata waktu balas">Libur CS</span>
                                    @endif
                                </div>
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
                                <p class="text-[10px] uppercase tracking-widest mt-1">Klik Perbarui Data atau ubah rentang tanggal.</p>
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
