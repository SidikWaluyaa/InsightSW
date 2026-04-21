<div class="max-w-[1600px] mx-auto space-y-10 pb-20 bg-[#F8FAFC] dark:bg-[#0B0E14] min-h-screen" wire:poll.30s="checkSync">
    
    {{-- 1. HERO HEADER (Premium Banner) --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-gradient-to-br from-[#10B981] via-[#059669] to-[#F59E0B] p-10 lg:p-16 shadow-2xl shadow-emerald-500/20 mt-4">
        {{-- Decorative Circles --}}
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-black/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-10">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 backdrop-blur-md">
                    <div class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></div>
                    <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Live Monitoring • UNIVERSE V2</span>
                </div>
                <h1 class="text-5xl lg:text-7xl font-black text-white tracking-tighter leading-none">Workshop<br>Dashboard</h1>
                <p class="text-lg text-emerald-50/70 font-medium max-w-md leading-relaxed">Laporan Hasil Kerja & Pantauan Bengkel (Waktu Nyata)</p>
            </div>

            <div class="space-y-5">
                {{-- Preset Filter Badges --}}
                @php
                    $filters = [
                        'hari_ini'   => 'Hari Ini',
                        'kemarin'    => 'Kemarin',
                        'minggu_ini' => 'Minggu Ini',
                        '7_hari'     => '7 Hari',
                        'bulan_ini'  => 'Bulan Ini',
                        '30_hari'    => '30 Hari',
                        '3_bulan'    => '3 Bulan',
                        'tahun_ini'  => 'Tahun Ini',
                    ];
                @endphp
                <div class="flex items-center gap-2 flex-wrap bg-black/20 backdrop-blur-xl p-2 rounded-2xl border border-white/10">
                    @foreach($filters as $key => $label)
                        <button wire:click="applyFilter('{{ $key }}')" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 
                            {{ $activeFilter === $key 
                                ? 'bg-white text-emerald-700 shadow-xl shadow-white/20 scale-105' 
                                : 'text-white/40 hover:text-white hover:bg-white/10' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-4">
                    {{-- Custom Date Range --}}
                    <div class="flex items-center gap-3 bg-white/95 backdrop-blur-md px-5 py-3.5 rounded-2xl shadow-xl border border-white shadow-emerald-900/10 flex-1 {{ $activeFilter === 'kustom' ? 'ring-2 ring-emerald-400/50' : '' }}">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <input type="date" wire:model.live="startDate" class="bg-transparent border-none p-0 focus:ring-0 text-xs font-black text-slate-800 uppercase tracking-widest w-full">
                        <span class="text-slate-300 font-black">—</span>
                        <input type="date" wire:model.live="endDate" class="bg-transparent border-none p-0 focus:ring-0 text-xs font-black text-slate-800 uppercase tracking-widest w-full">
                    </div>
                    
                    {{-- Sync Button --}}
                    <button wire:click="sync" wire:loading.attr="disabled" 
                        class="flex items-center gap-3 px-8 py-3.5 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all active:scale-95 shadow-2xl group shrink-0">
                        <svg wire:loading.class="animate-spin" class="w-5 h-5 text-emerald-400 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span wire:loading.remove>Tarik Data</span>
                        <span wire:loading>Memuat...</span>
                    </button>
                </div>

                {{-- Active Period Badge --}}
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-[9px] font-black text-white/70 uppercase tracking-[0.2em]">
                        Periode: {{ $this->filterLabel }} 
                        <span class="text-white/40 ml-1">({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('livewire.workshop_kpi_cards') --}}

    {{-- 3. SPK MATRIX INTELLIGENCE --}}
    <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-8 lg:p-12 border border-gray-100 dark:border-gray-800 shadow-sm border-b-4 border-b-indigo-500/20">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    </span>
                    Status Tahapan Kerja
                </h2>
                <p class="text-xs font-bold text-slate-400 mt-2 uppercase tracking-widest ml-13">Progres Pekerjaan & Pantauan Masalah</p>
            </div>
            <div class="px-5 py-2 rounded-full bg-slate-100 dark:bg-gray-800 text-[10px] font-black text-slate-600 dark:text-gray-400 uppercase tracking-widest border border-gray-200 dark:border-gray-700">
                Total: {{ $this->metrics->in_progress ?? 0 }} SPK
            </div>
        </div>

        <div class="relative grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Loading Overlay for Matrix --}}
            <div wire:loading wire:target="sync, startDate, endDate" class="absolute inset-0 z-50 bg-white/20 dark:bg-black/20 backdrop-blur-[2px] rounded-[3rem] flex items-center justify-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Memperbarui Matriks...</span>
                </div>
            </div>

            @php
                $phases = [
                    'Persiapan' => ['color' => 'indigo', 'title' => 'PENYIAPAN'],
                    'Sortir' => ['color' => 'rose', 'title' => 'PEMILIHAN'],
                    'Produksi' => ['color' => 'emerald', 'title' => 'PENGERJAAN'],
                    'Post' => ['color' => 'amber', 'title' => 'SELESAI'],
                ];
            @endphp

            @foreach($phases as $phaseName => $config)
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <span class="text-[11px] font-black text-{{ $config['color'] }}-600 uppercase tracking-[0.2em]">{{ $config['title'] }}</span>
                        @php
                            $phaseItems = $this->matrix->get($phaseName, collect());
                            $totalInPhase = $phaseItems->first()->total_group_at_sync ?? 0;
                        @endphp
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">{{ $totalInPhase }} SPK</span>
                    </div>
                    
                    <div class="space-y-4 p-5 rounded-[2rem] bg-slate-50/50 dark:bg-gray-800/20 border border-slate-100 dark:border-gray-800 min-h-[400px]">
                        @php
                            $followupItem = $phaseItems->where('sub_stage', 'Followup')->first();
                            $regularItems = $phaseItems->where('sub_stage', '!=', 'Followup');
                        @endphp

                        @foreach($regularItems as $item)
                            @include('livewire.workshop_matrix_item', ['item' => $item, 'config' => $config, 'total' => $totalInPhase])
                        @endforeach

                        @if($followupItem)
                            <div class="pt-4 mt-2 border-t border-dashed border-slate-200 dark:border-gray-700">
                                @include('livewire.workshop_matrix_item', ['item' => $followupItem, 'config' => ['color' => 'rose'], 'total' => $totalInPhase])
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="relative space-y-8">
        {{-- Loading Overlay for Analytics --}}
        <div wire:loading wire:target="sync, startDate, endDate" class="absolute inset-0 z-50 bg-white/10 dark:bg-black/10 backdrop-blur-[1px] rounded-[3rem] flex items-center justify-center">
            <div class="bg-white/90 dark:bg-gray-900/90 px-8 py-4 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                <div class="w-6 h-6 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-[10px] font-black text-slate-800 dark:text-white uppercase tracking-widest">Memperbarui Analitik...</span>
            </div>
        </div>

        {{-- 3. Flow Intelligence Hub (Replacing Area Chart) --}}
        <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-10 lg:p-14 border border-gray-100 dark:border-gray-800 shadow-sm mb-10 overflow-hidden relative group">
            {{-- Decorative Background Glow --}}
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all duration-1000"></div>
            
            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-14">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-4">
                            <span class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-xl shadow-emerald-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </span>
                            Kelancaran Arus Kerja
                        </h3>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 ml-16">Perbandingan Pekerjaan Masuk vs Hasil Jadi</p>
                    </div>

                    @php
                        $analytics = $this->trendAnalytics;
                        $perfVal = intval($analytics->performance_index ?? 0);
                        $statusText = $perfVal >= 80 ? 'LANCAR' : ($perfVal >= 50 ? 'NORMAL' : 'PADAT');
                        $statusColor = $perfVal >= 80 ? 'emerald' : ($perfVal >= 50 ? 'amber' : 'rose');
                    @endphp

                    @if($analytics)
                    <div class="flex items-center gap-4 px-6 py-3 bg-{{ $statusColor }}-500/5 border border-{{ $statusColor }}-500/10 rounded-[2rem]">
                        <div class="relative w-14 h-14 shrink-0">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-100 dark:stroke-gray-800" stroke-width="3"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-{{ $statusColor }}-500" stroke-width="3" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $perfVal }}" stroke-linecap="round"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[10px] font-black text-{{ $statusColor }}-600">{{ $perfVal }}%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Skor Kecepatan</p>
                            <h4 class="text-sm font-black text-{{ $statusColor }}-600 uppercase">{{ $statusText }}</h4>
                        </div>
                    </div>
                    @endif
                </div>

                @if($analytics)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-16">
                    {{-- Total Inbound --}}
                    <div class="relative p-8 rounded-[2.5rem] bg-slate-50/50 dark:bg-gray-800/20 border border-slate-100 dark:border-gray-800 group/card hover:border-indigo-500/30 transition-all">
                        <div class="absolute top-6 right-8 w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 group-hover/card:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Total Masuk</p>
                        <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ $analytics->total_in }} <span class="text-xs text-slate-400 tracking-normal ml-1">SPK</span></h3>
                        <p class="text-[9px] font-bold text-slate-400 mt-4 uppercase">Periode: {{ $this->filterLabel }}</p>
                    </div>

                    {{-- Completion --}}
                    <div class="relative p-8 rounded-[2.5rem] bg-slate-50/50 dark:bg-gray-800/20 border border-slate-100 dark:border-gray-800 group/card hover:border-emerald-500/30 transition-all">
                        <div class="absolute top-6 right-8 w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover/card:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Total Selesai</p>
                        <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ $analytics->total_out }} <span class="text-xs text-slate-400 tracking-normal ml-1">SPK</span></h3>
                        <p class="text-[9px] font-bold text-emerald-500 mt-4 uppercase">Success Rate: {{ $perfVal }}%</p>
                    </div>

                    {{-- Net Flow (Backlog Indicator) --}}
                    <div class="relative p-8 rounded-[2.5rem] bg-slate-50/50 dark:bg-gray-800/20 border border-slate-100 dark:border-gray-800 group/card hover:border-{{ $analytics->gap > 0 ? 'amber' : 'indigo' }}-500/30 transition-all">
                        <div class="absolute top-6 right-8 w-12 h-12 rounded-2xl bg-{{ $analytics->gap > 0 ? 'amber' : 'indigo' }}-500/10 flex items-center justify-center text-{{ $analytics->gap > 0 ? 'amber' : 'indigo' }}-500">
                            @if($analytics->gap > 0)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @endif
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Sisa Antrian</p>
                        <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ $analytics->gap > 0 ? '+' . $analytics->gap : $analytics->gap }} <span class="text-xs text-slate-400 tracking-normal ml-1">SPK</span></h3>
                        <p class="text-[9px] font-bold text-{{ $analytics->gap > 0 ? 'amber-500' : 'indigo-500' }} mt-4 uppercase">
                            {{ $analytics->gap > 0 ? 'Terdapat beban antrian baru' : 'Semua arus kerja terproses' }}
                        </p>
                    </div>
                </div>

                {{-- Daily/Weekly Pulse: Adaptive Matrix View --}}
                <div class="space-y-8">
                    <div class="flex items-center justify-between px-2">
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">
                                {{ $analytics->is_weekly ? 'Catatan Kecepatan Mingguan' : 'Catatan Kecepatan Harian' }}
                            </h4>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                {{ $analytics->is_weekly ? 'Ringkasan kelancaran kerja per minggu' : 'Status pengerjaan tim setiap hari' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-6 text-[9px] font-black uppercase tracking-widest text-slate-400">
                            <span class="flex items-center gap-2">🚀 Catch Up</span>
                            <span class="flex items-center gap-2">⚖️ Stable</span>
                            <span class="flex items-center gap-2">📥 Build Up</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
                        @foreach(collect($analytics->pulse)->take(7) as $day)
                            <div class="relative p-6 rounded-[2.5rem] bg-white dark:bg-gray-800 border {{ $day['status'] === 'CATCH UP' ? 'border-emerald-500/20 bg-emerald-500/[0.02]' : ($day['status'] === 'BUILD UP' ? 'border-amber-500/20 bg-amber-500/[0.02]' : 'border-slate-100 dark:border-gray-700') }} transition-all hover:-translate-y-1 hover:shadow-xl group">
                                {{-- Status Badge --}}
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1.5 rounded-full bg-{{ $day['status_color'] }}-500 text-white text-[9px] font-black uppercase tracking-widest shadow-lg shadow-{{ $day['status_color'] }}-500/30 flex items-center gap-2">
                                    <span>{{ $day['status_icon'] }}</span>
                                    <span>{{ $day['status'] }}</span>
                                </div>

                                <div class="mt-4 text-center space-y-4">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $day['label'] }}</p>
                                        <p class="text-[8px] font-bold text-slate-300 dark:text-gray-600 uppercase tracking-tighter">{{ $day['sub_label'] }}</p>
                                    </div>
                                    
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="flex items-center gap-3 text-2xl font-black text-slate-800 dark:text-white tracking-tighter">
                                            <span>{{ $day['in'] }}</span>
                                            <span class="text-slate-300 dark:text-gray-700 text-lg">➔</span>
                                            <span>{{ $day['out'] }}</span>
                                        </div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Input ➔ Hasil</p>
                                    </div>

                                    <div class="pt-4 border-t border-dashed border-slate-100 dark:border-gray-700">
                                        @if($day['delta'] > 0)
                                            <p class="text-[10px] font-black text-amber-500 uppercase">
                                                +{{ $day['delta'] }} Beban Baru
                                            </p>
                                        @elseif($day['delta'] < 0)
                                            <p class="text-[10px] font-black text-emerald-500 uppercase">
                                                -{{ abs($day['delta']) }} Antrian Berkurang
                                            </p>
                                        @else
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                Arus Seimbang
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Intelligence Insight Bar --}}
                <div class="mt-10 py-4 px-8 bg-slate-900 dark:bg-black rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-white uppercase tracking-widest leading-relaxed">
                            Info: Puncak kesibukan ada di tanggal <span class="text-emerald-400">{{ \Carbon\Carbon::parse($analytics->peak_day)->format('d M') }}</span> dengan <span class="text-emerald-400">{{ $analytics->peak_value }} pesanan baru</span>.
                        </p>
                    </div>
                    <div class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] shrink-0">
                        Velocity Check • Real-time Data
                    </div>
                </div>
                @else
                <div class="py-20 flex flex-col items-center justify-center text-slate-400 uppercase tracking-widest text-xs font-black gap-4 border-2 border-dashed border-slate-100 dark:border-gray-800 rounded-[2rem]">
                    <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Belum Ada Data Tren Periode Ini
                </div>
                @endif
            </div>
        </div>
        {{-- 4. MID ROW: Pipeline Doughnut (Wide) & Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-10">
            {{-- Status Pipeline --}}
            <div class="lg:col-span-4 bg-white dark:bg-gray-900 rounded-[3rem] p-10 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col items-center justify-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em] mb-10 w-full text-center relative z-10">Penyebaran Tugas</h4>
                <div id="workshopPipelineChart" class="w-full relative z-10" wire:ignore></div>
            </div>

            {{-- 5. SERVICES GRID (Full Width) --}}
            <div class="lg:col-span-8">
                {{-- Kategori Terlaris --}}
                <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-10 lg:p-14 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="flex items-center gap-6 mb-12 relative z-10">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Layanan Paling Laris</h4>
                            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Pendapatan Berdasarkan Jenis Layanan</p>
                        </div>
                    </div>
                    <div id="serviceMixChart" class="w-full flex-1 min-h-[500px] relative z-10" wire:ignore></div>
                </div>
            </div>
        </div>

    {{-- 5. WORKLOAD SECTION (Stations & Technicians) --}}
    <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-10 lg:p-14 border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-4">
                    <span class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-xl shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </span>
                    Beban Kerja Tim
                </h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 ml-16">Keseimbangan Pembagian Tugas & Stasiun</p>
            </div>
            @if(isset($this->metrics->workload['bottleneck']) && $this->metrics->workload['bottleneck']['count'] > 0)
                <div class="flex items-center gap-2 px-4 py-2 bg-rose-500/10 text-rose-500 rounded-xl border border-rose-500/20 text-[10px] font-black uppercase tracking-widest shrink-0">
                    <div class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></div>
                    BOTTLENECK: {{ $this->metrics->workload['bottleneck']['station'] }} ({{ $this->metrics->workload['bottleneck']['count'] }})
                </div>
            @else
                <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 text-emerald-500 rounded-xl border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    Alur Optimal
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            {{-- Antrian Per Ruangan --}}
            <div class="space-y-8">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] border-b border-slate-100 dark:border-gray-800 pb-4">Antrian Per Ruangan</h4>
                <div class="space-y-6">
                    @php
                        $workload = $this->metrics->workload ?? [];
                        $stations = $workload['stations'] ?? [];
                        $maxStation = count($stations) > 0 ? max($stations) : 100;
                    @endphp
                    @forelse($stations as $label => $val)
                        @include('livewire.workshop_workload_bar', [
                            'label' => $label,
                            'value' => $val,
                            'percentage' => ($val / ($maxStation ?: 1)) * 100,
                            'color' => $val > ($maxStation * 0.8) ? 'rose' : ($val > ($maxStation * 0.5) ? 'amber' : 'indigo')
                        ])
                    @empty
                        <p class="text-slate-400">Belum ada data stasiun</p>
                    @endforelse
                </div>
            </div>

            {{-- Beban Kerja Tiap Orang --}}
            <div class="space-y-8">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] border-b border-slate-100 dark:border-gray-800 pb-4">Beban Kerja Tiap Orang</h4>
                <div class="space-y-6">
                    @php
                        $technicians = $workload['technicians'] ?? [];
                        $maxTech = count($technicians) > 0 ? collect($technicians)->max('count') : 100;
                    @endphp
                    @forelse($technicians as $tech)
                        @include('livewire.workshop_workload_bar', [
                            'label' => $tech['name'],
                            'value' => $tech['count'],
                            'percentage' => ($tech['count'] / ($maxTech ?: 1)) * 100,
                            'color' => 'emerald'
                        ])
                    @empty
                        <p class="text-slate-400">Belum ada data teknisi</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- 6. OPERATIONS HUB (Bottom Grid) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- Urgent Orders --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
            <div class="p-8 pb-4 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-rose-500 flex items-center justify-center text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></span>
                    Pesanan Hampir Jatuh Tempo
                </h3>
                <span class="text-[10px] font-black text-rose-500 uppercase px-3 py-1 bg-rose-50 rounded-lg">Butuh Perhatian</span>
            </div>
            <div class="flex-1 overflow-y-auto px-8 pb-8 space-y-4 max-h-[500px]">
                @forelse($this->metrics->urgent_orders ?? [] as $ord)
                    <div class="p-5 rounded-3xl border {{ $ord['is_late'] ? 'bg-rose-50/40 border-rose-200 dark:bg-rose-900/10 dark:border-rose-900/40' : 'bg-slate-50/30 border-slate-100 dark:bg-gray-800/40 dark:border-gray-800' }} flex items-center justify-between group hover:shadow-lg transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-px h-10 bg-slate-200 dark:bg-gray-700"></div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 dark:text-white group-hover:text-rose-500 transition-colors">#{{ $ord['spk_number'] }} • {{ $ord['customer'] }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">{{ $ord['status'] }} • EST: {{ \Carbon\Carbon::parse($ord['est_date'])->format('d M') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($ord['is_late'])
                                <span class="px-4 py-1.5 bg-rose-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-500/20">TERLAMBAT</span>
                            @else
                                <span class="text-xs font-black text-amber-500 uppercase tracking-widest">{{ $ord['days_left'] }} Hari Lagi</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center py-10 text-slate-400 uppercase tracking-widest text-xs font-black">Aman! Tidak ada pesanan mendesak.</p>
                @endforelse
            </div>
        </div>

        {{-- Stock & Activity --}}
        <div class="space-y-8">
            {{-- Stock Alerts --}}
            <div class="bg-gradient-to-br from-orange-500 to-rose-600 rounded-[3rem] p-8 shadow-2xl shadow-orange-500/20 relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-[2s]"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    Bahan Mau Habis
                </h3>
                <div class="space-y-3">
                    @forelse($this->metrics->stock_alerts ?? [] as $stock)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 group-hover:bg-white/15 transition-all">
                            <p class="truncate text-xs font-black text-white uppercase">{{ $stock['name'] }}</p>
                            <span class="px-3 py-1 bg-white text-orange-600 rounded-lg text-[10px] font-black">Min: {{ $stock['min'] }}</span>
                        </div>
                    @empty
                        <p class="text-white/50 text-[10px] font-black text-center uppercase tracking-widest py-4 border border-dashed border-white/20 rounded-2xl leading-relaxed">Stok material <br> masih dalam batas aman.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-gray-900 rounded-[3rem] p-8 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Aktivitas Terbaru</h3>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase tracking-tighter">
                        <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></div>
                        Live
                    </div>
                </div>
                <div class="flex-1 space-y-6">
                    @forelse($this->metrics->recent_activity ?? [] as $act)
                        <div class="relative pl-6 before:absolute before:left-0 before:top-1.5 before:w-1.5 before:h-1.5 before:bg-indigo-500 before:rounded-full before:shadow-[0_0_8px_rgba(99,102,241,0.6)]">
                            <p class="text-[11px] font-black text-slate-800 dark:text-gray-100">{{ $act['user'] }} <span class="text-slate-400 font-bold ml-1">{{ $act['action'] }}</span></p>
                            <p class="text-[9px] font-black text-slate-400 uppercase mt-1">{{ $act['time'] }} • {{ $act['spk'] }}</p>
                        </div>
                    @empty
                        <p class="text-center py-10 text-slate-400 text-[10px] font-black uppercase">Belum ada aktivitas terbaru.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- STYLES --}}
    <style>
        @keyframes slide { from { background-position: 0 0; } to { background-position: 24px 24px; } }
        @keyframes pulse-rose {
            0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.4); border-color: rgba(244, 63, 94, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(244, 63, 94, 0); border-color: rgba(244, 63, 94, 0.4); }
            100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); border-color: rgba(244, 63, 94, 0.7); }
        }
        .anim-pulse-slow {
            animation: pulse-rose 3s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }
        .ml-13 { margin-left: 3.25rem; }
        .ml-16 { margin-left: 4rem; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>

    {{-- SCRIPTS (ApexCharts) - Inline, no @push needed --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let trendsChart, pipelineChart, serviceMixChart;

            const renderCharts = (data = null) => {
                const metrics = data || @json($this->metrics);
                if (!metrics) return;

                // 1. Trends Chart removed and replaced with Blade Native UI Components

                // 2. Pipeline Chart (Donut)
                const pipelineEl = document.querySelector("#workshopPipelineChart");
                if (pipelineEl && metrics.pipeline) {
                    const p = metrics.pipeline;
                    const pipelineOptions = {
                        series: [
                            p.assessment || 0,
                            p.preparation || 0,
                            p.sortir || 0,
                            p.production || 0,
                            p.qc || 0,
                            p.cx_follow_up || 0,
                            p.selesai || 0
                        ],
                        chart: { type: 'donut', height: 320 },
                        labels: ['Penilaian', 'Penyiapan', 'Pemilihan (Sortir)', 'Pengerjaan (Produksi)', 'Cek Kualitas (QC)', 'Tindak Lanjut', 'Selesai'],
                        colors: ['#6366f1', '#8b5cf6', '#f43f5e', '#10b981', '#0ea5e9', '#f59e0b', '#64748b'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '75%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '12px', fontWeight: 900, color: '#94a3b8', offsetY: -10 },
                                        value: { show: true, fontSize: '24px', fontWeight: 900, color: '#f1f5f9', offsetY: 10 },
                                        total: {
                                            show: true,
                                            label: 'TOTAL',
                                            fontSize: '10px',
                                            fontWeight: 900,
                                            color: '#94a3b8',
                                            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        }
                                    }
                                }
                            }
                        },
                        legend: { position: 'bottom', fontSize: '10px', fontWeight: 700, labels: { colors: '#94a3b8' } },
                        dataLabels: { enabled: false }
                    };
                    if (pipelineChart) pipelineChart.destroy();
                    pipelineChart = new ApexCharts(pipelineEl, pipelineOptions);
                    pipelineChart.render();
                }

                // 3. Service Mix Chart (Bar)
                const mixEl = document.querySelector("#serviceMixChart");
                const mix = metrics.service_mix || [];
                if (mixEl) {
                    if (mix.length > 0) {
                        const mixOptions = {
                            series: [{ name: 'Revenue', data: mix.map(i => i.revenue) }],
                            chart: { 
                                type: 'bar', 
                                height: 450, 
                                toolbar: { show: false },
                                animations: { enabled: true, easing: 'easeinout', speed: 800 }
                            },
                            plotOptions: { 
                                bar: { 
                                    horizontal: true, 
                                    borderRadius: 15, 
                                    barHeight: '75%', 
                                    distributed: true,
                                    dataLabels: { position: 'top' }
                                } 
                            },
                            colors: ['#f59e0b', '#10b981', '#6366f1', '#f43f5e', '#0ea5e9', '#8b5cf6', '#64748b', '#22c55e', '#3b82f6', '#ec4899'],
                            xaxis: { 
                                categories: mix.map(i => i.name),
                                labels: { 
                                    style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 800 },
                                    formatter: val => 'Rp ' + (val/1000000).toFixed(1) + 'M',
                                    offsetY: 5
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            xaxis: { 
                                categories: metrics.service_mix?.map(m => m.name) || [],
                                labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } } 
                            },
                            yaxis: { 
                                labels: { 
                                    style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 800 },
                                    maxWidth: 150
                                } 
                            },
                            grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 4 },
                            dataLabels: { 
                                enabled: true, 
                                position: 'top',
                                offsetX: 50,
                                formatter: val => 'Rp ' + (val/1000000).toFixed(1) + 'M', 
                                style: { fontSize: '10px', fontWeight: 900, colors: ['#64748b'] } 
                            },
                            legend: { show: false },
                            grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { left: 20 } }
                        };
                        if (serviceMixChart) serviceMixChart.destroy();
                        serviceMixChart = new ApexCharts(mixEl, mixOptions);
                        serviceMixChart.render();
                    } else {
                        mixEl.innerHTML = '<div class="h-full flex items-center justify-center text-slate-400 text-xs font-bold uppercase tracking-widest">Belum Ada Data</div>';
                    }
                }
            };

            renderCharts();
            
            Livewire.on('revenue-data-updated', (event) => {
                const metricsData = event.metrics || event[0]?.metrics || event;
                // Native Blade UI re-renders automatically with Livewire update
                // Re-rendering other JS charts:
                setTimeout(() => renderCharts(metricsData), 100);
            });

            // 60-Second Auto-Polling (Silent Sync)
            setInterval(() => {
                @this.sync(true);
            }, 60000);
        });
    </script>
</div>
