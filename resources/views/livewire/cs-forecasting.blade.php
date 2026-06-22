<div class="space-y-6 py-6" wire:init="loadData">
    @if (!$isLoaded)
        {{-- Skeleton Loaders --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 mb-8 animate-pulse">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-16 h-16 rounded-2xl bg-slate-800"></div>
                <div class="space-y-2">
                    <div class="h-6 w-48 bg-slate-800 rounded"></div>
                    <div class="h-3 w-80 bg-slate-800 rounded"></div>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
            </div>
        </div>
        <div class="h-20 bg-slate-900 border border-slate-800 rounded-3xl animate-pulse mb-6"></div>
        <div class="h-96 bg-slate-900 border border-slate-800 rounded-3xl animate-pulse"></div>

    @elseif ($errorMessage && empty($forecastingData))
        {{-- Alert error --}}
        <div class="p-6 bg-rose-950/20 border border-rose-900 rounded-3xl flex items-start gap-4">
            <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-lg text-rose-400">Gagal Memuat Data</h3>
                <p class="text-sm text-rose-500 mt-1">Sistem gagal mengambil data CS Forecasting dari API: {{ $errorMessage }}</p>
                <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                    Coba Lagi
                </button>
            </div>
        </div>
    @else
        {{-- Premium Dark Layout Container --}}
        <div class="text-slate-100 bg-[#070b13] border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            {{-- Header Background Glow --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>

            {{-- Mock Data Alert Pill --}}
            @if($isMock)
                <div class="mb-6 flex items-center justify-between bg-amber-500/10 border border-amber-500/20 px-4 py-2.5 rounded-xl">
                    <div class="flex items-center gap-2 text-xs font-semibold text-amber-400">
                        <span>⚠️</span>
                        <span>Mode Fallback / Mock Data Aktif karena API utama tidak terjangkau.</span>
                    </div>
                    <button wire:click="refreshData" class="text-xs font-black uppercase text-amber-400 hover:underline">
                        Coba Sinkron Ulang
                    </button>
                </div>
            @endif

            {{-- Header Row --}}
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 relative z-10">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-teal-950/40 text-teal-400 border border-teal-800/60 rounded-full text-[10px] font-black uppercase tracking-widest">
                            FORECASTING LAPORAN
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                        <span class="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">CS Forecasting</span> 
                        <span class="text-slate-100">&</span> 
                        <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Performance Analysis</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium max-w-3xl leading-relaxed">
                        Analisis data peramalan (forecasting) kinerja divisi Customer Service untuk melacak perbandingan closing, intake barang, dan performa omset keuangan secara bulanan.
                    </p>
                </div>

                {{-- Action Buttons (Top Right Corner) --}}
                <div class="flex items-center gap-3 self-end lg:self-auto">
                    <a href="{{ route('cs-kpi') }}" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-400 hover:to-indigo-500 text-white font-extrabold text-[11px] rounded-xl transition-all flex items-center gap-1.5 shadow-lg shadow-teal-500/10 border border-teal-400/20">
                        <span>📊</span>
                        <span>Lihat KPI Leaderboard</span>
                    </a>
                    
                    <button wire:click="refreshData" class="p-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 rounded-xl transition-all active:scale-95 text-slate-400 hover:text-white" title="Refresh Data">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- FILTER BAR (MATCHING SCREENSHOT 1) --}}
            <div class="bg-[#0c1322] border border-slate-800/80 p-5 rounded-3xl w-full flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8 relative z-10">
                <div class="flex flex-wrap items-center gap-6 w-full md:w-auto">
                    {{-- TAHUN LAPORAN --}}
                    <div class="flex flex-col min-w-[160px]">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 pl-1">TAHUN LAPORAN</span>
                        <div class="relative">
                            <select wire:model.live="year" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-300 focus:ring-0 focus:border-slate-700 outline-none appearance-none cursor-pointer">
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- BANDINGKAN DENGAN --}}
                    <div class="flex flex-col min-w-[200px]">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 pl-1">BANDINGKAN DENGAN</span>
                        <div class="relative">
                            <select wire:model.live="compareYear" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-300 focus:ring-0 focus:border-slate-700 outline-none appearance-none cursor-pointer">
                                <option value="">Tanpa Perbandingan</option>
                                <option value="2025">Tahun 2025</option>
                                <option value="2026">Tahun 2026</option>
                                <option value="2027">Tahun 2027</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEMESTER FILTER SEGMENTED BUTTONS --}}
                <div class="flex items-center bg-slate-950 border border-slate-850 p-1.5 rounded-2xl w-full md:w-auto self-stretch md:self-auto justify-between md:justify-start">
                    <button wire:click="setSemesterFilter('full')" 
                        class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all duration-300 {{ $semesterFilter === 'full' ? 'bg-[#0f242d] text-emerald-400 shadow-md border border-emerald-950' : 'text-slate-400 hover:text-white' }}">
                        Tahun Penuh (12 Bln)
                    </button>
                    <button wire:click="setSemesterFilter('s1')" 
                        class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all duration-300 {{ $semesterFilter === 's1' ? 'bg-[#0f242d] text-emerald-400 shadow-md border border-emerald-950' : 'text-slate-400 hover:text-white' }}">
                        Semester 1 (Jan - Jun)
                    </button>
                    <button wire:click="setSemesterFilter('s2')" 
                        class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all duration-300 {{ $semesterFilter === 's2' ? 'bg-[#0f242d] text-emerald-400 shadow-md border border-emerald-950' : 'text-slate-400 hover:text-white' }}">
                        Semester 2 (Jul - Des)
                    </button>
                </div>
            </div>

            {{-- Helper function block for comparisons --}}
            @php
                if (!function_exists('calcComparison')) {
                    function calcComparison($mainVal, $compareVal, $type = 'number') {
                        if ($compareVal === null || $compareVal === '') return null;
                        
                        $main = (float) $mainVal;
                        $comp = (float) $compareVal;
                        
                        $diff = $main - $comp;
                        $pctChange = ($comp > 0) ? ($diff / $comp) * 100 : 0;
                        
                        $colorClass = ($diff >= 0) ? 'text-emerald-400' : 'text-rose-500';
                        $sign = ($diff >= 0) ? '+' : '';
                        
                        if ($type === 'currency') {
                            $formattedComp = 'Rp ' . number_format($comp, 0, ',', '.');
                            $formattedDiff = $sign . 'Rp ' . number_format(abs($diff), 0, ',', '.');
                        } elseif ($type === 'pct') {
                            $formattedComp = number_format($comp, 2) . '%';
                            $formattedDiff = $sign . number_format($diff, 2) . '%';
                        } else {
                            $formattedComp = number_format($comp, 0, ',', '.');
                            $formattedDiff = $sign . number_format($diff, 0, ',', '.');
                        }
                        
                        $pctFormatted = $sign . number_format($pctChange, 1) . '%';
                        
                        return [
                            'compare' => $formattedComp,
                            'diff' => $formattedDiff,
                            'pct' => $pctFormatted,
                            'color' => $colorClass,
                            'is_positive' => $diff >= 0
                        ];
                    }
                }

                // Month Mapping
                $allMonths = [
                    ['index' => 0, 'name' => 'JANUARI'],
                    ['index' => 1, 'name' => 'FEBRUARI'],
                    ['index' => 2, 'name' => 'MARET'],
                    ['index' => 3, 'name' => 'APRIL'],
                    ['index' => 4, 'name' => 'MEI'],
                    ['index' => 5, 'name' => 'JUNI'],
                    ['index' => 6, 'name' => 'JULI'],
                    ['index' => 7, 'name' => 'AGUSTUS'],
                    ['index' => 8, 'name' => 'SEPTEMBER'],
                    ['index' => 9, 'name' => 'OKTOBER'],
                    ['index' => 10, 'name' => 'NOVEMBER'],
                    ['index' => 11, 'name' => 'DESEMBER'],
                ];

                if ($semesterFilter === 's1') {
                    $months = array_slice($allMonths, 0, 6);
                } elseif ($semesterFilter === 's2') {
                    $months = array_slice($allMonths, 6, 6);
                } else {
                    $months = $allMonths;
                }
            @endphp

            {{-- FORECASTING TABLE CARD --}}
            <div class="bg-[#0a0f1d] border border-slate-800/60 rounded-3xl p-6 shadow-xl relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-1.5 h-6 bg-[#22AF85] rounded-full"></span>
                    <h2 class="text-md font-extrabold uppercase tracking-wider text-white">
                        TABEL LAPORAN BULANAN
                    </h2>
                </div>

                {{-- Table Scroll Container --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1200px]">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="px-4 py-4 w-64 bg-[#0a0f1d] sticky left-0 z-20 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">METRIK</th>
                                @foreach($months as $month)
                                    <th class="px-4 py-4 text-center">
                                        <div class="text-slate-300 font-extrabold text-[11px]">{{ $month['name'] }}</div>
                                        <div class="text-[9px] text-slate-500 font-black mt-0.5">{{ $year }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850 font-black text-slate-300 text-xs">
                            
                            {{-- ROW: closing online --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">closing online</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_online'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_online'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-[#38bdf8]">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % closing online --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % closing online</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Online / Total Closing)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_online_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_online_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing ol/hari --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">└ closing ol/hari</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Online / Hari Aktif)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = round($forecastingData[$m['index']]['closing_online_per_day'] ?? 0);
                                        $compVal = !empty($compareYear) ? round($compareForecastingData[$m['index']]['closing_online_per_day'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-300">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing follow up --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">closing follow up</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_followup'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_followup'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-amber-400">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % closing follow up --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % closing follow up</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Follow Up / Total Closing)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_followup_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_followup_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-amber-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing fu/hari --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">└ closing fu/hari</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Follow Up / Hari Aktif)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = round($forecastingData[$m['index']]['closing_followup_per_day'] ?? 0);
                                        $compVal = !empty($compareYear) ? round($compareForecastingData[$m['index']]['closing_followup_per_day'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-300">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing offline --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">closing offline</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_offline'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_offline'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-violet-400">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % closing offline --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % closing offline</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Offline / Total Closing)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_offline_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_offline_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-violet-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing off/hari --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">└ closing off/hari</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Offline / Hari Aktif)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = round($forecastingData[$m['index']]['closing_offline_per_day'] ?? 0);
                                        $compVal = !empty($compareYear) ? round($compareForecastingData[$m['index']]['closing_offline_per_day'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-300">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing tidak kirim --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">closing tidak kirim</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_tidak_kirim'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_tidak_kirim'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-rose-400">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % closing tidak kirim --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % closing tidak kirim</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Tidak Kirim / (Online + FU))</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['closing_tidak_kirim_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['closing_tidak_kirim_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-rose-400/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: closing tidak kirim/hari --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">└ closing tidak kirim/hari</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Tidak Kirim / Hari Aktif)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = round($forecastingData[$m['index']]['closing_tidak_kirim_per_day'] ?? 0);
                                        $compVal = !empty($compareYear) ? round($compareForecastingData[$m['index']]['closing_tidak_kirim_per_day'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-300">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: total closing --}}
                            <tr class="bg-indigo-950/20 hover:bg-indigo-950/30 transition-all font-black text-white">
                                <td class="px-4 py-4 bg-[#080d19] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-extrabold capitalize text-indigo-300">total closing</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Online + FU + Offline)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['total_closing'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['total_closing'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-base font-extrabold font-mono text-indigo-400">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-400">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- SUBHEADER: TAHAP BARANG --}}
                            <tr class="bg-[#0e1726]/40 text-left text-[10px] font-black tracking-widest text-[#22AF85] uppercase">
                                <td colspan="{{ count($months) + 1 }}" class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    TAHAP BARANG
                                </td>
                            </tr>

                            {{-- ROW: sepatu masuk online & fu --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <span class="text-slate-100 font-extrabold text-sm capitalize">sepatu masuk online & fu</span>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['sepatu_masuk_online'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['sepatu_masuk_online'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-[#38bdf8]">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % sepatu online --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-12 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % sepatu online</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Sepatu Online / Total Sepatu Masuk)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['sepatu_online_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['sepatu_online_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: sepatu masuk offline --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <span class="text-slate-100 font-extrabold text-sm capitalize">sepatu masuk offline</span>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['sepatu_masuk_offline'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['sepatu_masuk_offline'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-violet-400">{{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % sepatu offline --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-12 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % sepatu offline</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Sepatu Offline / Total Sepatu Masuk)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['sepatu_offline_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['sepatu_offline_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-violet-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- SUBHEADER: TAHAP UANG --}}
                            <tr class="bg-[#0e1726]/40 text-left text-[10px] font-black tracking-widest text-[#22AF85] uppercase">
                                <td colspan="{{ count($months) + 1 }}" class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    TAHAP UANG
                                </td>
                            </tr>

                            {{-- ROW: omset total --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#22AF85]"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">omset total</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['omset_total'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['omset_total'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-emerald-400">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: terbayar --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">terbayar</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['terbayar'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['terbayar'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-teal-400">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % terbayar --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % terbayar</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Terbayar / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['terbayar_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['terbayar_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: total DP --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| total DP</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Pembayaran DP Awal)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['total_dp'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['total_dp'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-200">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % DP --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % DP</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Total DP / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['dp_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['dp_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: total lunas awal --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| total lunas awal</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Pembayaran 100% Upfront)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['total_lunas_awal'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['total_lunas_awal'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-200">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % lunas awal --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % lunas awal</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Total Lunas Awal / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['lunas_awal_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['lunas_awal_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: total pelunasan --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| total pelunasan</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Pembayaran Akhir Pelunasan)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['total_pelunasan'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['total_pelunasan'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-slate-200">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % pelunasan --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % pelunasan</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Total Pelunasan / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['pelunasan_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['pelunasan_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-[#06b6d4]">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: tambah jasa --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">tambah jasa</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['tambah_jasa'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['tambah_jasa'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-amber-500">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % tambah jasa --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % tambah jasa</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Tambah Jasa / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['tambah_jasa_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['tambah_jasa_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-amber-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: OTO --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">OTO</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['oto'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['oto'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-violet-400">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % OTO --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % OTO</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(OTO / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['oto_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['oto_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-violet-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: ongkir --}}
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="px-4 py-3 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                        <span class="text-slate-100 font-extrabold text-sm capitalize">ongkir</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = (float) ($forecastingData[$m['index']]['ongkir'] ?? 0);
                                        $compVal = !empty($compareYear) ? (float) ($compareForecastingData[$m['index']]['ongkir'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'currency');
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-bold font-mono text-blue-400">Rp {{ number_format($mainVal, 0, ',', '.') }}</span>
                                        @if($comp)
                                            <div class="text-[10px] font-bold font-mono mt-1 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ROW: % Ongkir --}}
                            <tr class="hover:bg-slate-900/10 transition-all text-slate-400">
                                <td class="px-4 py-2.5 pl-8 bg-[#0a0f1d] sticky left-0 z-10 shadow-[4px_0_10px_-3px_rgba(0,0,0,0.4)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold">| % Ongkir</span>
                                        <span class="text-[9px] text-slate-500 font-medium">(Ongkir / Omset Total)</span>
                                    </div>
                                </td>
                                @foreach($months as $m)
                                    @php
                                        $mainVal = $forecastingData[$m['index']]['ongkir_pct'] ?? 0;
                                        $compVal = !empty($compareYear) ? ($compareForecastingData[$m['index']]['ongkir_pct'] ?? 0) : null;
                                        $comp = calcComparison($mainVal, $compVal, 'pct');
                                    @endphp
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-bold font-mono text-blue-500/80">{{ number_format($mainVal, 2, ',', '.') }}%</span>
                                        @if($comp)
                                            <div class="text-[9px] font-semibold font-mono mt-0.5 text-slate-500">
                                                vs {{ $comp['compare'] }} (<span class="{{ $comp['color'] }}">{{ $comp['pct'] }}</span>)
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- FOOTER FOOTNOTES (MATCHING SCREENSHOT 6) --}}
                <div class="mt-8 pt-6 border-t border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
                    <div>
                        * CLOSING TIDAK KIRIM: SPK SEPATU BERSTATUS SPK PENDING (DIAMBIL DARI TABEL WORK_ORDERS).
                    </div>
                    <div>
                        * TOTAL CLOSING BALANCE: ONLINE + FU + OFFLINE.
                    </div>
                </div>

            </div>

        </div>
    @endif

    {{-- CSS styles for sticky columns, borders, and layouts --}}
    <style>
        .divide-slate-850 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgba(30, 41, 59, 0.4);
        }
        .bg-slate-850 {
            background-color: rgba(30, 41, 59, 0.3);
        }
        /* Style for sticky column shadow */
        th.sticky, td.sticky {
            position: sticky;
            left: 0;
            z-index: 10;
        }
    </style>
</div>
