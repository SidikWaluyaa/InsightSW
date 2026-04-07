<div class="px-6 py-6 pb-20" x-data="{ showFilters: false }">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">Quality Control</h2>
            <span class="px-2 py-1 rounded-lg bg-indigo-500 text-white text-[10px] font-black tracking-[0.2em]">AFTER</span>
        </div>
    </x-slot>

    {{-- SECTION: KPI WIDGETS (4-WIDGET SHIFT PERFORMANCE SYSTEM) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Widget 1: Baseline Per Hari (Snapshot) --}}
        <div class="bg-slate-800 rounded-[40px] p-8 shadow-xl relative overflow-hidden group border border-slate-700 flex flex-col justify-between">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-slate-700/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4 gap-2">
                    <input type="date" wire:model.live="widgetDate" 
                           class="bg-slate-700 border-none rounded-xl text-[10px] font-bold text-slate-300 focus:ring-0 cursor-pointer px-2 py-1 transition-all hover:bg-slate-600">
                    <button wire:click="takeManualSnapshot" 
                            class="p-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-slate-300 transition-all active:scale-90"
                            title="Ambil Snapshot Sekarang">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-white tracking-tighter">{{ number_format($w1_baseline) }}</span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Order</span>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">DIPOTRET: {{ $w1_time }}</p>
            </div>
        </div>

        {{-- Widget 2: Real-time Verified (REACTIVE TO DROPDOWNS) --}}
        <div class="bg-white dark:bg-slate-900 border border-emerald-500/20 rounded-[40px] p-8 shadow-xl shadow-emerald-500/5 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Terverifikasi</span>
                        @if(array_filter($filters))
                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase rounded-lg">Filtered</span>
                        @endif
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($w2_realtime_verified) }}</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Order</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Live Data</span>
                </div>
            </div>
        </div>

        {{-- Widget 3: Belum Verifikasi (REACTIVE TO DROPDOWNS) --}}
        <div class="bg-white dark:bg-slate-900 border border-amber-500/20 rounded-[40px] p-8 shadow-xl shadow-amber-500/5 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em]">Antrean QC</span>
                        @if(array_filter($filters))
                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 text-[8px] font-black uppercase rounded-lg">Scoped</span>
                        @endif
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">{{ number_format($w3_total_backlog) }}</span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Antrean</span>
                    </div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-4 flex items-center gap-1">
                    <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Sisa Hutang Verifikasi
                </p>
            </div>
        </div>

        {{-- Widget 4: SHIFT ACHIEVEMENT (GLOBAL TOTAL PROGRESS) --}}
        <div class="bg-indigo-600 rounded-[40px] p-8 shadow-xl shadow-indigo-500/30 relative overflow-hidden group border border-white/10 flex flex-col justify-between">
            <div class="absolute -right-4 -bottom-4 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <span class="text-[10px] font-black text-indigo-100 uppercase tracking-[0.2em] mb-4 block">Shift Achievement</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black text-white tracking-tighter">{{ ($w4_shift_achievement >= 0 ? '+' : '') . number_format($w4_shift_achievement) }}</span>
                        <span class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Order</span>
                    </div>
                </div>
                <div class="mt-4 p-2 bg-white/10 rounded-xl backdrop-blur-sm self-start">
                    <p class="text-[9px] font-black text-indigo-50 uppercase tracking-widest">
                        TOTAL TEAM PROGRESS
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: INPUT HEADER --}}
    <div class="mb-10 bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-slate-200/40 dark:shadow-none">
        <div class="flex flex-col lg:flex-row lg:items-end gap-6 justify-between">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Spreadsheet URL --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Spreadsheet URL</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </div>
                        <input type="text" 
                               wire:model="spreadsheetUrl"
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white"
                               placeholder="https://docs.google.com/spreadsheets/d/...">
                    </div>
                    @error('spreadsheetUrl') <span class="text-rose-500 text-[10px] ml-1">{{ $message }}</span> @enderror
                </div>

                {{-- GID --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">GID (Sheet ID)</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <input type="text" 
                               wire:model="gid"
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-800/50 border-none rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white"
                               placeholder="1019775130">
                    </div>
                    @error('gid') <span class="text-rose-500 text-[10px] ml-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button @click="showFilters = !showFilters" 
                        class="px-4 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl font-black text-xs uppercase tracking-widest transition-all hover:bg-indigo-500 hover:text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>FILTER</span>
                </button>

                <button wire:click="fetch(true)" 
                        wire:loading.attr="disabled"
                        class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-3">
                    <span wire:loading.remove wire:target="fetch">TARIK DATA</span>
                    <span wire:loading wire:target="fetch">MEMUAT...</span>
                    <svg wire:loading.remove wire:target="fetch" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <svg wire:loading wire:target="fetch" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
        </div>

        {{-- DYNAMIC FILTERS DRAWER --}}
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Filter: Jenis Barang --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kategori Barang</label>
                <select wire:model.live="filters.jenis_barang" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <option value="">SEMUA KATEGORI</option>
                    @foreach($availableOptions['jenis_barang'] as $opt)
                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter: Status --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status Operasional</label>
                <select wire:model.live="filters.status" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <option value="">SEMUA STATUS</option>
                    @foreach($availableOptions['status'] as $opt)
                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter: Step --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Step Progress</label>
                <select wire:model.live="filters.step" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <option value="">SEMUA STEP</option>
                    @foreach($availableOptions['step'] as $opt)
                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter: Checklist --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Verifikasi Checklist</label>
                <select wire:model.live="filters.checklist" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <option value="">SEMUA STATUS VERIFIKASI</option>
                    @foreach($availableOptions['checklist'] as $opt)
                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 pt-6 border-t border-gray-100 dark:border-gray-800">
                <div class="lg:col-span-2 mb-2">
                    <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">Analisis Periode (Hanya Widget)</h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">* Filter ini hanya mengubah angka pada Widget STATISTIK di atas, tabel data di bawah tetap menampilkan semua history.</p>
                </div>
                
                {{-- Filter: Start Date --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mulai Dari</label>
                    <input type="date" 
                           wire:model.live="startDate"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                </div>

                {{-- Filter: End Date --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Hingga Tanggal</label>
                    <input type="date" 
                           wire:model.live="endDate"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                </div>
            </div>

            <div class="lg:col-span-4 flex justify-between items-center mt-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                    * Format tanggal spreadsheet: Month/Day/Year
                </div>
                <button wire:click="resetFilters" 
                        class="text-[10px] font-black text-rose-500 hover:text-rose-600 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    BERSIHKAN FILTER
                </button>
            </div>
        </div>

        @if($errorMessage)
            <div class="mt-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-500 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $errorMessage }}
            </div>
        @endif
    </div>

    {{-- SECTION: DATA TABLE --}}
    <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden">
        {{-- SEARCH BAR --}}
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/30 dark:bg-slate-800/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Daftar QC</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total: {{ number_format($totalResults) }} Data</p>
                </div>
            </div>

            <div class="relative group w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm"
                       class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border-none rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white shadow-sm"
                       placeholder="Cari ID, SPK, atau Nama...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">SPK Number</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Barang</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status / Step</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tgl Kirim</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Checklist</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                    @forelse($paginatedItems as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-8 py-6">
                                <span class="text-sm font-black text-slate-800 dark:text-white">#{{ $item['id'] }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">{{ $item['spk_number'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-xs font-bold text-slate-500 dark:text-gray-400">{{ $item['jenis_barang'] }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ $item['customer_name'] }}</span>
                                    <span class="text-[10px] font-semibold text-slate-400">{{ $item['customer_phone'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] font-black rounded-lg uppercase tracking-tight">{{ $item['status'] }}</span>
                                    <span class="text-slate-300 dark:text-gray-700">/</span>
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-500 text-[10px] font-black rounded-lg uppercase tracking-tight">{{ $item['step'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-semibold text-xs text-slate-500 dark:text-gray-400">
                                {{ $item['tanggal_kirim'] }}
                            </td>
                            <td class="px-6 py-6">
                                @if($item['raw_checklist'] === 'TRUE')
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest leading-none">
                                            {{ $item['checklist'] }}
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <span class="text-[11px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest leading-none">
                                            {{ $item['checklist'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                @if($item['link_pdf'])
                                    <a href="{{ $item['link_pdf'] }}" target="_blank" 
                                       class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-600 hover:text-white transition-all inline-flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span class="text-[10px] font-black uppercase">Report</span>
                                    </a>
                                @else
                                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">No PDF</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-8 py-32 text-center">
                                <div class="flex flex-col items-center justify-center gap-6">
                                    <div class="w-24 h-24 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center text-slate-200 dark:text-slate-700">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-lg font-black text-slate-400 dark:text-slate-600 uppercase tracking-widest">No Data Found</p>
                                        <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">
                                            {{ $searchTerm ? "Tidak ada hasil untuk '$searchTerm'" : "Masukkan URL Spreadsheet dan GID lalu klik Tarik Data." }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION FOOTER --}}
        @if($totalResults > $perPage)
            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Halaman {{ $currentPage }} dari {{ $totalPages }}
                </div>
                
                <div class="flex items-center gap-2">
                    <button wire:click="setPage({{ max(1, $currentPage - 1) }})" 
                            {{ $currentPage <= 1 ? 'disabled' : '' }}
                            class="p-2.5 rounded-xl transition-all {{ $currentPage <= 1 ? 'text-slate-300' : 'text-indigo-500 hover:bg-indigo-500 hover:text-white bg-white dark:bg-slate-800 shadow-sm' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div class="flex items-center gap-1 mx-2">
                        @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                            <button wire:click="setPage({{ $i }})" 
                                    class="w-10 h-10 rounded-xl text-xs font-black transition-all 
                                    {{ $currentPage == $i ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>

                    <button wire:click="setPage({{ min($totalPages, $currentPage + 1) }})" 
                            {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                            class="p-2.5 rounded-xl transition-all {{ $currentPage >= $totalPages ? 'text-slate-300' : 'text-indigo-500 hover:bg-indigo-500 hover:text-white bg-white dark:bg-slate-800 shadow-sm' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
