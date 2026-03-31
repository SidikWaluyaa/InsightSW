<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Meta Ads Insights
            </h2>
        </div>
    </x-slot>

    <div class="space-y-4">
        {{-- Control Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 mb-2">
            <div>
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-none mb-1">Kontrol Iklan Meta</h2>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-black uppercase tracking-widest">Live Sync from Meta Graph API</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="sync" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-emerald-500/20 transition-all duration-300 active:scale-95 disabled:opacity-75">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span wire:loading.remove>Sync Data</span>
                    <span wire:loading>Syncing...</span>
                </button>
            </div>
        </div>

    {{-- Hub Area --}}
    <div class="relative bg-slate-900 rounded-[2rem] p-6 mb-2 overflow-hidden border border-white/5 shadow-2xl">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-gradient-to-br from-slate-800 to-slate-900 rounded-[1.5rem] border border-white/10 shadow-inner group transition-all duration-500 hover:scale-105 active:scale-95">
                    <svg class="w-6 h-6 text-indigo-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tighter uppercase italic leading-none mb-1">Meta Analytics Hub</h1>
                    <p class="text-[9px] font-black text-indigo-400/60 uppercase tracking-[0.2em]">Real-time Performance Data</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-10">
                <div class="flex flex-col items-end">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5 mr-1">Total Results</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white tracking-tighter">{{ number_format($metaSummary['total_results'] ?? 0, 0, ',', '.') }}</span>
                        <span class="text-[10px] font-black text-emerald-500 uppercase italic tracking-widest">Conv.</span>
                    </div>
                </div>
                <div class="w-px h-10 bg-white/10 hidden md:block"></div>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5 mr-1">Avg. Cost Per Result</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Rp</span>
                        <span class="text-3xl font-black text-white tracking-tighter">{{ number_format($metaSummary['avg_cost_per_result'] ?? 0, 0, ',', '.') }}</span>
                        <span class="text-[9px] font-black text-indigo-500 uppercase italic tracking-widest">Precision</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scoreboard Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-2">
        <div class="relative bg-white dark:bg-gray-900 rounded-[1.5rem] p-6 border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-20 h-20 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </div>
            <div class="flex items-center gap-5">
                <div class="p-3 bg-emerald-500/10 rounded-xl border border-emerald-500/20 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5 block">Total Spend</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-[10px] font-black text-gray-400 uppercase leading-none">Rp</span>
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">{{ number_format($metaSummary['total_spend'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative bg-white dark:bg-gray-900 rounded-[1.5rem] p-6 border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-20 h-20 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
            </div>
            <div class="flex items-center gap-5">
                <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5 block">Total Impressions</span>
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">{{ number_format($metaSummary['total_impressions'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Campaign Spend Breakdown --}}
    @if(count($campaignSpend) > 0)
    <div class="px-2 pb-4 overflow-hidden">
        <div class="flex items-center gap-2 mb-3 px-2">
            <div class="w-1 h-3 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
            <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">Budget Breakdown per Campaign</span>
        </div>
        <div class="flex gap-4 overflow-x-auto pb-2 custom-scrollbar snap-x">
            @foreach($campaignSpend as $item)
                <div class="flex-none w-72 p-4 rounded-[1.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/5 shadow-sm hover:shadow-md transition-all group snap-start">
                    <div class="flex items-start justify-between mb-3">
                        <div class="p-2 bg-emerald-500/5 rounded-xl border border-emerald-500/10 transition-colors group-hover:bg-emerald-500/10">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                        </div>
                        <span class="text-[9px] font-black py-0.5 px-2 bg-gray-50 dark:bg-emerald-500/10 text-gray-500 dark:text-emerald-400 rounded-full uppercase tracking-tighter">
                            {{ number_format(($item['total_spend'] / ($metaSummary['total_spend'] ?: 1)) * 100, 1) }}% Share
                        </span>
                    </div>
                    <h4 class="text-[11px] font-black text-gray-900 dark:text-white uppercase leading-tight mb-2 line-clamp-1 group-hover:text-emerald-500 transition-colors">
                        {{ $item['campaign_name'] }}
                    </h4>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Rp</span>
                        <span class="text-xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">
                            {{ number_format($item['total_spend'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Premium Filter & Toolbar Section --}}
    <div class="bg-slate-900/40 backdrop-blur-md rounded-[2rem] p-5 border border-white/5 shadow-2xl mb-2">
        <div class="flex flex-wrap items-center gap-6">
            {{-- Date Context Group --}}
            <div class="flex items-center gap-3 bg-white/5 p-1.5 rounded-2xl border border-white/5">
                <div class="flex items-center gap-2 px-3 py-1">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Periode</span>
                </div>
                <div class="flex items-center gap-1">
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-0 text-[11px] font-black text-white focus:ring-0 p-1 w-32 cursor-pointer hover:bg-white/5 rounded-lg transition-colors">
                    <span class="text-gray-600 text-xs">—</span>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-0 text-[11px] font-black text-white focus:ring-0 p-1 w-32 cursor-pointer hover:bg-white/5 rounded-lg transition-colors">
                </div>
            </div>

            <div class="h-10 w-px bg-white/10 hidden xl:block"></div>

            {{-- Core Filters Group --}}
            <div class="flex flex-1 flex-wrap items-center gap-4">
                {{-- Campaign Select --}}
                <div class="flex-1 min-w-[200px] relative group">
                    <select wire:model.live="selectedCampaign" class="w-full bg-white/5 border-white/10 rounded-xl text-[11px] font-black text-white py-2 pl-4 pr-10 focus:border-blue-500/50 focus:ring-0 hover:bg-white/10 transition-all appearance-none cursor-pointer">
                        <option value="" class="bg-slate-900">SEMUA KAMPANYE</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign }}" class="bg-slate-900">{{ strtoupper($campaign) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-hover:text-blue-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>

                {{-- Adset Select --}}
                <div class="flex-1 min-w-[200px] relative group">
                    <select wire:model.live="selectedAdset" class="w-full bg-white/5 border-white/10 rounded-xl text-[11px] font-black text-white py-2 pl-4 pr-10 focus:border-blue-500/50 focus:ring-0 hover:bg-white/10 transition-all appearance-none cursor-pointer">
                        @if($selectedCampaign)
                            <option value="" class="bg-slate-900">SEMUA ADSET</option>
                            @foreach($adsets as $adset)
                                <option value="{{ $adset }}" class="bg-slate-900">{{ strtoupper($adset) }}</option>
                            @endforeach
                        @else
                            <option value="" class="bg-slate-900 text-gray-500">PILIH KAMPANYE</option>
                        @endif
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-hover:text-blue-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>

                {{-- Expanded Search --}}
                <div class="flex-[1.5] min-w-[260px] relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="CARI NAMA IKLAN..." class="w-full bg-white/5 border-white/10 rounded-xl text-[11px] font-black text-white py-2 pl-11 focus:border-blue-500/50 focus:ring-0 hover:bg-white/10 transition-all placeholder:text-gray-600">
                </div>
            </div>

            {{-- Action Group --}}
            <div class="flex items-center gap-3">
                <button wire:click="resetFilters" class="px-5 py-2 bg-white/5 hover:bg-red-500/10 text-gray-400 hover:text-red-500 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 border border-white/5">
                    Reset
                </button>
                
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="px-5 py-2 flex items-center gap-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-400/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-indigo-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        <span>Kolom</span>
                    </button>
                    
                    {{-- Column Popover --}}
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-4 w-72 bg-slate-900 border border-white/10 rounded-2xl shadow-2xl z-[100] overflow-hidden backdrop-blur-xl" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                        <div class="px-5 py-3 text-[10px] font-black uppercase text-blue-400 tracking-widest border-b border-white/5">Konfigurasi Data</div>
                        <div class="max-h-80 overflow-y-auto p-5 space-y-5 custom-scrollbar text-[11px] font-black">
                            @foreach(['Performance' => ['results', 'reach', 'frequency', 'cost_per_result', 'budget', 'spend', 'stop_time'], 'Awareness' => ['impressions', 'cpm'], 'Links' => ['link_click', 'cpc', 'ctr']] as $cat => $cols)
                                <div class="space-y-3">
                                    <h4 class="text-[9px] font-black uppercase text-gray-500 opacity-60 flex items-center gap-2">
                                        <span class="w-1 h-1 bg-blue-500 rounded-full"></span>
                                        {{ $cat }}
                                    </h4>
                                    @foreach($cols as $col)
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <span class="text-gray-400 group-hover:text-white transition-colors uppercase tracking-tight">{{ str_replace('_', ' ', $col) }}</span>
                                            <input type="checkbox" wire:click="toggleColumn('{{ $col }}')" {{ in_array($col, $selectedColumns) ? 'checked' : '' }} class="w-4 h-4 rounded border-white/10 text-blue-600 focus:ring-0 bg-white/5">
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Area with Breadcrumbs --}}
    <div class="mt-8">
        {{-- Hierarchical Breadcrumbs --}}
        <div class="flex items-center gap-3 mb-6 px-4 py-2.5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm w-fit">
            <button wire:click="resetNavigation" class="flex items-center gap-2 group">
                <div class="p-1.5 rounded-lg bg-gray-100 group-hover:bg-blue-500 group-hover:text-white transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
            </button>
            
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            
            <button wire:click="resetNavigation" class="text-[10px] font-black uppercase tracking-widest {{ !$selectedCampaign ? 'text-blue-500' : 'text-gray-400 hover:text-gray-600 transition-colors' }}">
                Campaigns
            </button>

            @if($selectedCampaign)
                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <button wire:click="selectCampaign('{{ addslashes($selectedCampaign) }}')" class="text-[10px] font-black uppercase tracking-widest {{ !$selectedAdset ? 'text-blue-500' : 'text-gray-400 hover:text-gray-600 transition-colors' }}">
                    {{ $selectedCampaign }}
                </button>
            @endif

            @if($selectedAdset)
                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-500">
                    {{ $selectedAdset }}
                </span>
            @endif
        </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden scroll-smooth">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse table-auto">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 shadow-[4px_0_10px_rgba(0,0,0,0.05)] px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800">
                            @if($groupBy === 'campaign')
                                Campaign Overview
                            @elseif($groupBy === 'adset')
                                Adsets in {{ \Illuminate\Support\Str::limit($selectedCampaign, 15) }}
                            @else
                                Ad Performance Details
                            @endif
                        </th>
                        
                        @foreach($selectedColumns as $col)
                            <th class="px-6 py-4 text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tighter border-b border-gray-100 dark:border-gray-800 text-center group/h">
                                <span class="group-hover/h:text-blue-500 transition-colors">
                                    {{ str_replace(['_all', '_'], [' (all)', ' '], $col) }}
                                </span>
                            </th>
                        @endforeach

                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($reports as $report)
                        @php
                            $clickAction = "";
                            if ($groupBy === 'campaign') $clickAction = "selectCampaign('".addslashes($report->campaign_name)."')";
                            elseif ($groupBy === 'adset') $clickAction = "selectAdset('".addslashes($report->adset_name)."')";
                            else $clickAction = "openDetail(".$report->id.")";
                        @endphp
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-500/5 transition-all duration-200 group/row cursor-pointer border-b border-gray-50 dark:border-gray-800"
                            wire:click="{{ $clickAction }}" wire:key="row-{{ $report->id }}-{{ $groupBy }}">
                            <td class="sticky left-0 z-30 bg-white group-hover/row:bg-slate-50 dark:bg-gray-900 dark:group-hover/row:bg-slate-800 shadow-[6px_0_15px_rgba(0,0,0,0.05)] px-6 py-4 border-r border-gray-100 dark:border-gray-800 min-w-[350px] max-w-[400px]">
                                <div class="flex flex-col">
                                    <div class="max-w-[300px]">
                                        @if($groupBy === 'campaign')
                                            <button wire:click="selectCampaign('{{ addslashes($report->campaign_name) }}')" class="text-xs font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter leading-relaxed text-left hover:underline decoration-2 underline-offset-4 decoration-blue-500/30 transition-all">
                                                {{ $report->campaign_name }}
                                            </button>
                                        @elseif($groupBy === 'adset')
                                            <button wire:click="selectAdset('{{ addslashes($report->adset_name) }}')" class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tighter leading-relaxed text-left hover:underline decoration-2 underline-offset-4 decoration-indigo-500/30 transition-all">
                                                {{ $report->adset_name }}
                                            </button>
                                        @else
                                            <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-tighter leading-relaxed whitespace-normal break-words">
                                                {{ $report->ad_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Dynamic Columns matching Meta's order --}}
                            @foreach($selectedColumns as $col)
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $val = $report->$col;
                                        $isMoney = str_contains($col, 'spend') || str_contains($col, 'budget') || str_contains($col, 'cost') || $col === 'cpc' || $col === 'cpm' || $col === 'cpc_all';
                                        $isPercent = str_contains($col, 'ctr') || $col === 'ctr_all';
                                        $isHighFreq = $col === 'frequency';
                                    @endphp

                                    <span class="text-xs font-bold {{ $col === 'results' || $col === 'spend' ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-gray-600 dark:text-gray-300' }}">
                                        @if($col === 'status')
                                            @php $status = strtoupper($val ?: 'UNKNOWN'); @endphp
                                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black tracking-widest border {{ $status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-gray-500/10 text-gray-500 border-white/5' }}">
                                                {{ $status }}
                                            </span>
                                        @elseif($isMoney)
                                            <span class="text-[9px] font-medium text-gray-400 not-italic mr-0.5">Rp</span>{{ number_format((float) $val, 0, ',', '.') }}
                                        @elseif($isPercent)
                                            {{ number_format((float) $val, 2) }}<span class="text-[9px] font-medium text-gray-400 ml-0.5">%</span>
                                        @elseif($isHighFreq)
                                            {{ number_format((float) $val, 2) }}
                                        @elseif($col === 'stop_time')
                                            <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest italic">{{ $val ?: 'Ongoing' }}</span>
                                        @else
                                            {{ number_format((float) $val, 0, ',', '.') }}
                                        @endif
                                    </span>
                                </td>
                            @endforeach

                            <td class="px-6 py-4 text-center">
                                <button wire:click="showDetail({{ $report->id }})" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors group/btn">
                                    <svg class="w-4 h-4 text-gray-400 group-hover/btn:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium italic">Belum ada data Meta Ads yang disinkronkan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Slide-over: Premium Redesign --}}
    <div x-data="{ open: @entangle('showDetail') }" 
        x-show="open" 
        class="fixed inset-0 overflow-hidden z-[100]" 
        x-cloak>
        <div class="absolute inset-0 overflow-hidden bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="$wire.closeDetail()" x-transition.opacity></div>
        
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-2xl transform transition duration-500 ease-in-out sm:duration-700" 
                x-show="open"
                x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                x-transition:enter-start="translate-x-full" 
                x-transition:enter-end="translate-x-0" 
                x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                x-transition:leave-start="translate-x-0" 
                x-transition:leave-end="translate-x-full">
                
                <div class="flex h-full flex-col bg-slate-950 shadow-2xl border-l border-white/5">
                    {{-- Header Area --}}
                    <div class="relative px-6 py-10 bg-gradient-to-br from-indigo-900/40 via-blue-900/20 to-slate-950 text-white overflow-hidden">
                        {{-- Background Glow --}}
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl"></div>
                        
                        @if($viewingData)
                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-500/10 rounded-xl border border-blue-400/20">
                                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-300">
                                            {{ $viewingData['is_aggregated'] ? 'Campaign Summary' : 'Ad Performance' }}
                                        </span>
                                    </div>
                                    <button @click="$wire.closeDetail()" class="p-2 hover:bg-white/5 rounded-2xl transition-all hover:rotate-90">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <h3 class="text-2xl font-black italic tracking-tight mb-4">
                                    {{ $viewingData['is_aggregated'] ? $viewingData['campaign_name'] : $viewingData['ad_name'] }}
                                </h3>
                                
                                <div class="flex flex-wrap gap-2">
                                    @isset($viewingData['is_precision'])
                                        <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-500/20 shadow-lg shadow-emerald-500/5">Meta Precision Active</span>
                                    @endisset
                                    <span class="px-3 py-1 bg-white/5 text-gray-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-white/5">
                                        @if($viewingData['is_aggregated']) Consolidated Range @else {{ \Carbon\Carbon::parse($viewingData['date'])->format('d M Y') }} @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Data Content --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-8 space-y-10 pb-20">
                        @if($viewingData)
                            @foreach([
                                [
                                    'title' => 'Performance & Cost',
                                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'color' => 'emerald',
                                    'fields' => ['spend', 'results', 'cost_per_result']
                                ],
                                [
                                    'title' => 'Awareness & Reach',
                                    'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
                                    'color' => 'blue',
                                    'fields' => ['impressions', 'reach', 'frequency']
                                ],
                                [
                                    'title' => 'Engagement Metrics',
                                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                    'color' => 'indigo',
                                    'fields' => ['clicks', 'link_click', 'link_click_unique', 'ctr', 'cpc', 'cpm']
                                ],
                                [
                                    'title' => 'Video Performance',
                                    'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'color' => 'violet',
                                    'fields' => ['video_view', 'video_p25', 'video_p50', 'video_p75', 'video_p100']
                                ]
                            ] as $section)
                                <div>
                                    <div class="flex items-center gap-2 mb-4 border-b border-white/5 pb-2">
                                        <svg class="w-4 h-4 text-{{ $section['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}" /></svg>
                                        <h4 class="text-[10px] font-black uppercase text-gray-300 tracking-widest">{{ $section['title'] }}</h4>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach($section['fields'] as $field)
                                            <div class="bg-white/[0.04] p-4 rounded-2xl border border-white/5 hover:border-white/10 transition-colors group/card shadow-sm">
                                                <p class="text-[9px] font-black uppercase text-gray-500 tracking-tighter mb-1.5 group-hover/card:text-gray-400 transition-colors">
                                                    {{ str_replace(['_', 'video_p'], [' ', 'P'], $field) }}
                                                </p>
                                                <p class="text-sm font-black text-white italic tracking-tight">
                                                    @if(str_contains($field, 'spend') || str_contains($field, 'cost') || ($field === 'cpc' || $field === 'cpm'))
                                                        <span class="text-[10px] font-bold text-gray-500 not-italic mr-0.5">Rp</span>{{ number_format($viewingData[$field] ?? 0, 0, ',', '.') }}
                                                    @elseif(str_contains($field, 'frequency') || $field === 'ctr')
                                                        {{ number_format($viewingData[$field] ?? 0, 2) }}@if($field === 'ctr')<span class="text-[10px] font-bold text-gray-500">%</span>@endif
                                                    @else
                                                        {{ number_format($viewingData[$field] ?? 0, 0, ',', '.') }}
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
    </style>
    </div>
</div>
