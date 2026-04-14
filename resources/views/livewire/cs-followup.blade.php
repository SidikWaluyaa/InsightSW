@use('Carbon\Carbon')
<div class="space-y-8 animate-in fade-in duration-700" x-data="{ showFilters: false }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Followup Command Center</h1>
            <p class="text-slate-500 dark:text-gray-400 mt-1 font-medium italic">Prioritaskan pelanggan yang membutuhkan respon segera untuk menjaga kepuasan.</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto items-stretch">
            {{-- Advanced Filter Button --}}
            <button @click="showFilters = true" 
                class="px-6 py-3 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl border border-gray-200 dark:border-gray-800 rounded-2xl text-xs font-black uppercase tracking-[0.2em] text-slate-600 dark:text-gray-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <span>Filters</span>
            </button>

            {{-- Search Input --}}
            <div class="relative group w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-indigo-400 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" 
                    class="block w-full pl-11 pr-4 py-3 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl border border-gray-200 dark:border-gray-800 rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all placeholder-slate-400 dark:text-white" 
                    placeholder="Cari Nama, No HP, atau Email...">
            </div>

            {{-- Export Dropdown --}}
            <div class="relative" x-data="{ exportOpen: false }">
                <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                    class="h-full px-6 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-slate-900/20 dark:shadow-white/10 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>EXPORT</span>
                </button>

                <div x-show="exportOpen" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="absolute right-0 mt-3 w-48 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden">
                    
                    <button wire:click="exportExcel" @click="exportOpen = false"
                        class="w-full px-5 py-4 text-left hover:bg-emerald-50 dark:hover:bg-emerald-500/10 flex items-center gap-3 transition-colors group">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black uppercase text-slate-700 dark:text-slate-200">Excel Format</span>
                            <span class="text-[9px] font-bold text-slate-400 capitalize">spreadsheet (.xlsx)</span>
                        </div>
                    </button>

                    <button wire:click="exportPdf" @click="exportOpen = false"
                        class="w-full px-5 py-4 text-left hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center gap-3 transition-colors group border-t border-slate-50 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black uppercase text-slate-700 dark:text-slate-200">PDF Report</span>
                            <span class="text-[9px] font-bold text-slate-400 capitalize">print ready (.pdf)</span>
                        </div>
                    </button>
                    
                    <div wire:loading wire:target="exportExcel, exportPdf" class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 backdrop-blur-[1px] flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span class="text-[8px] font-black uppercase tracking-widest text-indigo-500">Generating...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ADVANCED FILTER DRAWER --}}
    <template x-teleport="body">
        <div x-show="showFilters" class="fixed inset-0 z-[100] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <div x-show="showFilters" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showFilters = false"></div>
                
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="showFilters" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-slate-900 shadow-2xl">
                            <div class="px-8 py-10">
                                <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-8">
                                    <div>
                                        <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tighter" id="slide-over-title">Advanced Config</h2>
                                        <p class="mt-2 text-xs font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Personalisasi tampilan data dan laporan ekspor Anda.</p>
                                    </div>
                                    <button @click="showFilters = false" class="rounded-2xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-rose-500 transition-all">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>

                                <div class="mt-10 space-y-8">
                                    {{-- Date Range --}}
                                    <div class="space-y-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Rentang Tanggal (Interaksi)</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <span class="text-[9px] font-bold text-slate-400 ml-1">MULAI</span>
                                                <input type="date" wire:model.live="startDate" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white">
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[9px] font-bold text-slate-400 ml-1">SAMPAI</span>
                                                <input type="date" wire:model.live="endDate" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- PIC & Team --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-4">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">PIC (Owner)</label>
                                            <select wire:model.live="selectedPic" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white appearance-none">
                                                <option value="">Semua PIC</option>
                                                @foreach($pics as $pic) <option value="{{ $pic }}">{{ $pic }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-4">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Tim</label>
                                            <select wire:model.live="selectedTeam" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white appearance-none">
                                                <option value="">Semua Tim</option>
                                                @foreach($teams as $team) <option value="{{ $team }}">{{ $team }}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div class="space-y-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Status Chat</label>
                                        <select wire:model.live="selectedStatus" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white appearance-none">
                                            <option value="">Semua Status</option>
                                            @foreach($statuses as $status) <option value="{{ $status }}">{{ $status }}</option> @endforeach
                                        </select>
                                    </div>

                                    {{-- Priority & Source --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-4">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Prioritas</label>
                                            <select wire:model.live="selectedPriority" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white appearance-none">
                                                <option value="">Semua</option>
                                                @foreach($priorities as $p) <option value="{{ $p }}">{{ $p }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-4">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Sumber Lead</label>
                                            <select wire:model.live="selectedSource" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all dark:text-white appearance-none">
                                                <option value="">Semua</option>
                                                @foreach($sources as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-10 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 grid grid-cols-2 gap-4">
                                <button @click="showFilters = false" class="px-6 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-emerald-500 transition-all">Simpan & Tutup</button>
                                <button wire:click="resetFilters" class="px-6 py-4 bg-rose-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-500/30 active:scale-95 transition-all">Reset Semua</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Urgent Card --}}
        <div class="relative group overflow-hidden bg-white dark:bg-gray-900 rounded-[2rem] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-rose-500/5 transition-all duration-500 hover:-translate-y-1">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-rose-500/10 rounded-full blur-3xl group-hover:bg-rose-500/20 transition-all duration-500"></div>
            <div class="relative flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-500 ring-4 ring-rose-500/5 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-rose-500/80 uppercase tracking-widest mb-1">Diatas 7 Hari</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($kpis['urgent']) }}</span>
                        <span class="text-sm font-bold text-slate-400">Pesan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warning Card --}}
        <div class="relative group overflow-hidden bg-white dark:bg-gray-900 rounded-[2rem] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-amber-500/5 transition-all duration-500 hover:-translate-y-1">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl group-hover:bg-amber-500/20 transition-all duration-500"></div>
            <div class="relative flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 ring-4 ring-amber-500/5 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-amber-500/80 uppercase tracking-widest mb-1">Diatas 3 Hari</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($kpis['warning']) }}</span>
                        <span class="text-sm font-bold text-slate-400">Pesan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="relative group overflow-hidden bg-white dark:bg-gray-900 rounded-[2rem] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-indigo-500/5 transition-all duration-500 hover:-translate-y-1">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl group-hover:bg-indigo-500/20 transition-all duration-500"></div>
            <div class="relative flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 ring-4 ring-indigo-500/5 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-indigo-500/80 uppercase tracking-widest mb-1">Diatas 1 Hari</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($kpis['info']) }}</span>
                        <span class="text-sm font-bold text-slate-400">Pesan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs & Table --}}
    <div class="bg-white/50 dark:bg-gray-900/50 backdrop-blur-3xl rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-wrap gap-2">
                @foreach (['all' => 'Semua Antrean', 'urgent' => '🔴 Urgent (>7H)', 'warning' => '🟡 Warning (>3H)', 'info' => '🔵 Info (>1H)'] as $key => $label)
                    <button wire:click="setTab('{{ $key }}')" 
                        class="px-6 py-2.5 rounded-full text-xs font-black uppercase tracking-[0.2em] transition-all duration-300 {{ $activeTab === $key ? 'bg-slate-900 dark:bg-white text-white dark:text-gray-950 shadow-lg scale-105' : 'text-slate-500 hover:bg-slate-200/50 dark:hover:bg-gray-800' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            
            <div class="flex items-center gap-3 px-4 py-2 bg-emerald-500/10 rounded-full border border-emerald-500/20">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Data Real-time</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-950/50">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Info Pelanggan</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Status Chat</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Interaksi Terakhir</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Response Gap</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Prioritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($contacts as $contact)
                        @php
                            $custDate = $contact->last_contact_from_customers ? Carbon::parse($contact->last_contact_from_customers) : null;
                            $compDate = $contact->last_contacted_from_company ? Carbon::parse($contact->last_contacted_from_company) : null;
                            
                            $isWaiting = $custDate && (!$compDate || $custDate->gt($compDate));
                            $gapDays = $isWaiting ? round($custDate->diffInHours(now()) / 24) : 0;
                            
                            $color = 'slate';
                            $label = 'Normal';
                            if ($gapDays >= 7) { $color = 'rose'; $label = 'URGENT'; }
                            elseif ($gapDays >= 3) { $color = 'amber'; $label = 'WARNING'; }
                            elseif ($gapDays >= 1) { $color = 'indigo'; $label = 'INFO'; }
                        @endphp
                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-blue-500/10 flex items-center justify-center text-indigo-500 font-black text-lg group-hover:scale-110 transition-transform duration-500">
                                        {{ substr($contact->first_name ?: '?', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-emerald-500 transition-colors">{{ $contact->first_name }} {{ $contact->last_name }}</span>
                                        <span class="text-xs text-slate-400 font-medium">{{ $contact->phone_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-gray-800 text-slate-500 border border-slate-200 dark:border-gray-700">
                                    {{ $contact->status_chat ?: 'New Member' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium italic">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Cust: {{ $custDate ? $custDate->translatedFormat('d M, H:i') : '-' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium italic">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        You: {{ $compDate ? $compDate->translatedFormat('d M, H:i') : '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="text-lg font-black text-{{ $color }}-500">
                                        {{ $gapDays }} <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold ml-1">Hari</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex h-3 w-3">
                                        @if($gapDays >= 1)
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $color }}-400 opacity-75"></span>
                                        @endif
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-{{ $color }}-500"></span>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-{{ $color }}-500">{{ $label }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-100 dark:bg-gray-800 rounded-full flex items-center justify-center text-slate-300">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-400 font-black uppercase tracking-[0.3em] text-xs">Semua Beres! Tidak ada antrean.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($contacts->hasPages())
            <div class="p-8 border-t border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-950/30">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>
