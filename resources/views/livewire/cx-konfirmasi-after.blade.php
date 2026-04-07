<div class="px-6 py-6 pb-20" x-data="{ showFilters: false }">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">Konfirmasi</h2>
            <span class="px-2 py-1 rounded-lg bg-teal-500 text-white text-[10px] font-black tracking-[0.2em]">AFTER</span>
        </div>
    </x-slot>

    {{-- SECTION: KPI WIDGETS (3 LIVE WIDGETS) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Widget 1: Total Data --}}
        <div class="bg-white dark:bg-slate-900 border border-teal-500/20 rounded-[40px] p-8 shadow-xl shadow-teal-500/5 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-teal-500/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-teal-500 uppercase tracking-[0.2em]">Total Data</span>
                        @if(array_filter($filters) || $startDate || $endDate)
                            <span class="px-2 py-0.5 bg-teal-500/10 text-teal-500 text-[8px] font-black uppercase rounded-lg tracking-widest">Filtered</span>
                        @endif
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($totalData) }}</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Customer</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Live Sync Data</span>
                </div>
            </div>
        </div>

        {{-- Widget 2: Sudah Respon --}}
        <div class="bg-white dark:bg-slate-900 border border-emerald-500/20 rounded-[40px] p-8 shadow-xl shadow-emerald-500/5 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Sudah Respon</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($totalRespon) }}</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Customer</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Goal Achievement</span>
                </div>
            </div>
        </div>

        {{-- Widget 3: Belum Respon --}}
        <div class="bg-white dark:bg-slate-900 border border-amber-500/20 rounded-[40px] p-8 shadow-xl shadow-amber-500/5 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em]">Belum Respon</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($totalBelum) }}</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hutang</span>
                    </div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-4 flex items-center gap-1">
                    <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Perlu Follow Up
                </p>
            </div>
        </div>
    </div>

    {{-- SECTION: SECOND TIER WIDGETS (DYNAMIC CATEGORIES) --}}
    @if(count($statusCounts) > 0)
    <div class="flex flex-wrap gap-6 mb-10 pb-4">
        @foreach($statusCounts as $label => $count)
            @php
                $color = 'indigo';
                $lLabel = strtolower($label);
                if (str_contains($lLabel, 'puas') && !str_contains($lLabel, 'kurang')) $color = 'emerald';
                elseif (str_contains($lLabel, 'komplain') || str_contains($lLabel, 'kurang puas')) $color = 'rose';
                elseif (str_contains($lLabel, 'no respon') || str_contains($lLabel, 'hold')) $color = 'amber';
            @endphp
            <div class="flex-1 min-w-[200px] bg-white dark:bg-slate-900 border border-{{ $color }}-500/10 rounded-[32px] p-7 shadow-xl shadow-{{ $color }}-500/5 flex flex-col justify-center transition-all hover:scale-[1.02] hover:shadow-{{ $color }}-500/10 group">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-2 h-2 rounded-full bg-{{ $color }}-500 animate-pulse"></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] truncate group-hover:text-{{ $color }}-500 transition-colors">{{ $label }}</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($count) }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Case</span>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- SECTION: FILTER BAR --}}
    <div class="mb-10 bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-slate-200/40 dark:shadow-none">
        <div class="flex flex-col lg:flex-row lg:items-end gap-6 justify-between">
            <div class="flex flex-wrap items-center gap-4 flex-1">
                <button @click="showFilters = !showFilters" 
                        class="px-5 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl font-black text-xs uppercase tracking-[0.1em] transition-all hover:bg-teal-500 hover:text-white flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>CONFIG FILTERS</span>
                </button>

                <button wire:click="fetch" 
                        wire:loading.attr="disabled"
                        class="px-8 py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-black text-xs uppercase tracking-[0.1em] shadow-lg shadow-teal-500/30 transition-all active:scale-95 flex items-center gap-3">
                    <span wire:loading.remove wire:target="fetch">TARIK DATA LIVE</span>
                    <span wire:loading wire:target="fetch">SYNCING...</span>
                    <svg wire:loading.remove wire:target="fetch" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <svg wire:loading wire:target="fetch" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>

            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Last Sync: {{ now()->format('H:i:s') }}
            </div>
        </div>

        {{-- DYNAMIC FILTERS DRAWER --}}
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Search --}}
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Keywords (Name / SPK / Catatan)</label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchTerm"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white"
                           placeholder="Filter data by any keyword...">
                </div>

                {{-- Start Date --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mulai Tanggal</label>
                    <input type="date" 
                           wire:model.live="startDate"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                </div>

                {{-- End Date --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai Tanggal</label>
                    <input type="date" 
                           wire:model.live="endDate"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Filter: PIC --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category: PIC</label>
                    <select wire:model.live="filters.pic" 
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                        <option value="">SEMUA PIC</option>
                        @foreach($availableOptions['pic'] as $opt)
                            <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: Respon --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category: Status Respon</label>
                    <select wire:model.live="filters.respon_customer" 
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                        <option value="">SEMUA STATUS</option>
                        @foreach($availableOptions['respon_customer'] as $opt)
                            <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: Tahap Lanjutan --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category: Tahap Lanjutan</label>
                    <select wire:model.live="filters.tahap_lanjutan" 
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                        <option value="">SEMUA TAHAP</option>
                        @foreach($availableOptions['tahap_lanjutan'] as $opt)
                            <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: No SPK --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quick Select: SPK ID</label>
                    <select wire:model.live="filters.no_spk" 
                            class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-[13px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all dark:text-white">
                        <option value="">SEMUA SPK</option>
                        @foreach($availableOptions['no_spk'] as $opt)
                            <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/20 p-5 rounded-3xl">
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    * Dynamic options apply based on live sheet data. Minimum range: Jan 2026.
                </div>
                <button wire:click="resetFilters" 
                        class="px-6 py-3 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    BERSIHKAN SEMUA FILTER
                </button>
            </div>
        </div>
    </div>

    {{-- SECTION: DATA TABLE --}}
    <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-slate-200/40 dark:shadow-none overflow-hidden">
        {{-- SEARCH BAR --}}
        <div class="px-8 py-7 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-50/40 dark:bg-slate-800/10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-teal-500/10 flex items-center justify-center text-teal-600 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-black text-slate-800 dark:text-white uppercase tracking-wider leading-none mb-1">Daftar Konfirmasi</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        Displaying {{ number_format($paginatedItems ? count($paginatedItems) : 0) }} of {{ number_format($totalResults) }} Results
                    </p>
                </div>
            </div>

            <div class="relative group w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400 group-focus-within:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm"
                       class="w-full pl-12 pr-5 py-3.5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-2xl text-[13px] font-bold focus:ring-4 focus:ring-teal-500/10 transition-all dark:text-white shadow-sm"
                       placeholder="Cari Nama, No SPK, atau Deskripsi...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-8 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">No</th>
                        <th class="px-6 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu & SPK</th>
                        <th class="px-6 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Customer</th>
                        <th class="px-6 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest">Handled By</th>
                        <th class="px-6 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tahap Lanjutan</th>
                        <th class="px-6 py-7 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Respon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                    @forelse($paginatedItems as $index => $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-teal-500/5 transition-all group">
                            <td class="px-8 py-6 text-center">
                                <span class="text-xs font-black text-slate-300 dark:text-slate-700">{{ ($currentPage - 1) * $perPage + $index + 1 }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[13px] font-black text-slate-800 dark:text-white">{{ $item['tanggal'] }}</span>
                                    <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-500 text-[10px] font-black rounded-md w-fit tracking-wider">{{ $item['no_spk'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-semibold">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $item['nama_customer'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        <span class="text-[11px] font-black text-slate-400">{{ $item['kontak_customer'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-teal-500/10 flex items-center justify-center text-teal-600 font-black text-[10px] border border-teal-500/20 shadow-sm">
                                        {{ strtoupper(substr($item['pic'] ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="text-xs font-black text-slate-600 dark:text-gray-300 uppercase tracking-tighter">{{ $item['pic'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-black rounded-xl uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                                    {{ $item['tahap_lanjutan'] }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                @if($item['has_respon'])
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-500 shrink-0 shadow-sm rotate-3 group-hover:rotate-0 transition-transform">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-tight">{{ $item['respon_customer'] }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[150px]">{{ $item['catatan_gudang'] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-500 shrink-0 shadow-sm -rotate-3 group-hover:rotate-0 transition-transform animate-pulse">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"></path></svg>
                                        </div>
                                        <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest italic">Belum Konfirmasi</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-40 text-center">
                                <div class="flex flex-col items-center justify-center gap-8">
                                    <div class="relative">
                                        <div class="w-32 h-32 rounded-full bg-slate-50 dark:bg-slate-800/40 flex items-center justify-center text-slate-200 dark:text-slate-700 animate-pulse">
                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-teal-500 rounded-full flex items-center justify-center text-white font-black text-xl animate-bounce shadow-lg">?</div>
                                    </div>
                                    <div class="space-y-3">
                                        <p class="text-xl font-black text-slate-400 dark:text-slate-600 uppercase tracking-[0.3em]">No Data Discovered</p>
                                        <p class="text-sm font-bold text-slate-400 dark:text-slate-500 max-w-md mx-auto leading-relaxed">
                                            {{ $searchTerm ? "We couldn't find any results for '$searchTerm'. Try adjusting your filters or checking the date range." : "Please click 'Tarik Data Live' to synchronize your dashboard with the Google Sheet." }}
                                        </p>
                                        <button wire:click="resetFilters" class="text-teal-500 font-black text-[11px] uppercase tracking-widest hover:underline mt-4">Reset all filters</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($totalResults > $perPage)
            <div class="px-8 py-8 bg-slate-50/50 dark:bg-slate-800/40 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="px-3 py-1 bg-white dark:bg-slate-800 rounded-lg shadow-sm">PAGE {{ $currentPage }}</span>
                    <span>OF {{ $totalPages }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <button wire:click="setPage({{ max(1, $currentPage - 1) }})" 
                            {{ $currentPage <= 1 ? 'disabled' : '' }}
                            class="p-3.5 rounded-2xl transition-all {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed bg-slate-100 text-slate-400' : 'text-teal-600 hover:bg-teal-600 hover:text-white bg-white dark:bg-slate-800 shadow-md active:scale-90 border border-slate-100 dark:border-slate-700' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div class="flex items-center gap-2 mx-4">
                        @php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                        @endphp
                        @if($start > 1)
                             <button wire:click="setPage(1)" class="w-11 h-11 rounded-2xl text-[13px] font-black text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">1</button>
                             <span class="text-slate-300 font-black">...</span>
                        @endif
                        @for($i = $start; $i <= $end; $i++)
                            <button wire:click="setPage({{ $i }})" 
                                    class="w-11 h-11 rounded-2xl text-[13px] font-black transition-all shadow-sm
                                    {{ $currentPage == $i ? 'bg-teal-600 text-white shadow-lg shadow-teal-500/40 scale-110' : 'text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-800 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800' }}">
                                {{ $i }}
                            </button>
                        @endfor
                        @if($end < $totalPages)
                             <span class="text-slate-300 font-black">...</span>
                             <button wire:click="setPage({{ $totalPages }})" class="w-11 h-11 rounded-2xl text-[13px] font-black text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">{{ $totalPages }}</button>
                        @endif
                    </div>

                    <button wire:click="setPage({{ min($totalPages, $currentPage + 1) }})" 
                            {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                            class="p-3.5 rounded-2xl transition-all {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed bg-slate-100 text-slate-400' : 'text-teal-600 hover:bg-teal-600 hover:text-white bg-white dark:bg-slate-800 shadow-md active:scale-90 border border-slate-100 dark:border-slate-700' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
