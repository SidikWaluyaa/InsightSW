@use('Carbon\Carbon')
<div class="space-y-6" x-data="{ showFilters: false }">

    {{-- Page Header with Filter, Search & Export Actions --}}
    <x-ui.page-header title="Followup" subtitle="Prioritaskan pelanggan yang membutuhkan respon segera untuk menjaga kepuasan.">
        <div class="flex flex-col sm:flex-row gap-2.5 w-full lg:w-auto items-stretch sm:items-center">
            {{-- Advanced Filter Button --}}
            <button type="button" @click="showFilters = true" 
                class="inline-flex items-center justify-center gap-2 h-11 px-4 bg-white dark:bg-[#1e2336] hover:bg-slate-50 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10 hover:border-emerald-500/40 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 transition-all shadow-sm">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <span>FILTERS</span>
                @if($selectedPic || $selectedTeam || $selectedStatus || $selectedPriority || $selectedSource || $startDate || $endDate)
                    <span class="w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-emerald-500/20"></span>
                @endif
            </button>

            {{-- Search Input --}}
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" 
                    class="w-full h-11 pl-10 pr-4 bg-white dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm transition-all" 
                    placeholder="Cari Nama, No HP, atau Email...">
            </div>

            {{-- Export Dropdown --}}
            <div class="relative" x-data="{ exportOpen: false }">
                <button type="button" @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                    class="w-full sm:w-auto h-11 px-5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm shadow-indigo-500/25 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>EXPORT</span>
                </button>

                <div x-show="exportOpen" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="absolute right-0 mt-2 w-52 bg-white dark:bg-[#1e2336] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl z-50 overflow-hidden divide-y divide-gray-100 dark:divide-white/5"
                    style="display: none;">
                    
                    <button type="button" wire:click="exportExcel" @click="exportOpen = false"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-3 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Excel Format</span>
                            <span class="text-[10px] text-slate-400">Spreadsheet (.xlsx)</span>
                        </div>
                    </button>

                    <button type="button" wire:click="exportPdf" @click="exportOpen = false"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-3 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">PDF Report</span>
                            <span class="text-[10px] text-slate-400">Print ready (.pdf)</span>
                        </div>
                    </button>
                    
                    <div wire:loading wire:target="exportExcel, exportPdf" class="absolute inset-0 bg-white/70 dark:bg-[#1e2336]/80 backdrop-blur-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Exporting...</span>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.page-header>

    {{-- Advanced Filter Drawer --}}
    <template x-teleport="body">
        <div x-show="showFilters" class="fixed inset-0 z-[100] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="absolute inset-0 overflow-hidden">
                <div x-show="showFilters" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showFilters = false"></div>
                
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="showFilters" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                        class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-auto bg-white dark:bg-[#151928] border-l border-gray-200 dark:border-white/10 shadow-2xl">
                            <div class="px-6 py-6 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider" id="slide-over-title">Filter Lanjutan</h2>
                                    <p class="text-xs text-slate-400 mt-0.5">Personalisasi tampilan antrean & ekspor data.</p>
                                </div>
                                <button type="button" @click="showFilters = false" class="rounded-xl p-2 text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-rose-400 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="p-6 space-y-5 flex-1">
                                {{-- Rentang Tanggal Interaksi --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rentang Tanggal (Interaksi)</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase">Mulai</span>
                                            <input type="date" wire:model.live="startDate" class="w-full mt-1 px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase">Sampai</span>
                                            <input type="date" wire:model.live="endDate" class="w-full mt-1 px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>

                                {{-- PIC & Tim --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">PIC (Owner)</label>
                                        <select wire:model.live="selectedPic" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500">
                                            <option value="">Semua PIC</option>
                                            @foreach($pics as $pic) <option value="{{ $pic }}">{{ $pic }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tim</label>
                                        <select wire:model.live="selectedTeam" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500">
                                            <option value="">Semua Tim</option>
                                            @foreach($teams as $team) <option value="{{ $team }}">{{ $team }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Status Chat --}}
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Chat</label>
                                    <select wire:model.live="selectedStatus" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500">
                                        <option value="">Semua Status</option>
                                        @foreach($statuses as $status) <option value="{{ $status }}">{{ $status }}</option> @endforeach
                                    </select>
                                </div>

                                {{-- Prioritas & Sumber Lead --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Prioritas</label>
                                        <select wire:model.live="selectedPriority" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500">
                                            <option value="">Semua</option>
                                            @foreach($priorities as $p) <option value="{{ $p }}">{{ $p }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sumber Lead</label>
                                        <select wire:model.live="selectedSource" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-white focus:border-indigo-500">
                                            <option value="">Semua</option>
                                            @foreach($sources as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 border-t border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-[#151928] grid grid-cols-2 gap-3">
                                <button type="button" @click="showFilters = false" class="px-4 py-2.5 bg-white dark:bg-[#1e2336] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:border-emerald-500 transition-colors">
                                    Terapkan
                                </button>
                                <button type="button" wire:click="resetFilters" class="px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm shadow-rose-500/20 active:scale-95 transition-all">
                                    Reset Semua
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- KPI Metric Cards (Uniform 3-card grid matching Analitik Sleekflow) --}}
    @php
        $cards = [
            [
                'label' => 'Diatas 7 Hari',
                'value' => number_format($kpis['urgent']),
                'sub' => 'Prioritas Tertinggi (Urgent)',
                'note' => 'Pelanggan menunggu balasan lebih dari 7 hari',
                'glow' => 'bg-rose-500/10',
                'iconBg' => 'bg-rose-500/20 text-rose-400',
                'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            ],
            [
                'label' => 'Diatas 3 Hari',
                'value' => number_format($kpis['warning']),
                'sub' => 'Perlu Perhatian (Warning)',
                'note' => 'Pelanggan menunggu balasan antara 3 - 7 hari',
                'glow' => 'bg-amber-500/10',
                'iconBg' => 'bg-amber-500/20 text-amber-400',
                'iconPath' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Diatas 1 Hari',
                'value' => number_format($kpis['info']),
                'sub' => 'Antrean Harian (Info)',
                'note' => 'Pelanggan menunggu balasan antara 1 - 3 hari',
                'glow' => 'bg-blue-500/10',
                'iconBg' => 'bg-blue-500/20 text-blue-400',
                'iconPath' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($cards as $c)
            <div wire:key="cs-card-{{ Str::slug($c['label']) }}" class="bg-white dark:bg-[#1e2336] rounded-2xl p-5 border border-gray-100 dark:border-white/5 relative overflow-hidden group flex flex-col shadow-sm transition-all hover:border-white/10">
                <div class="absolute top-0 right-0 w-24 h-24 {{ $c['glow'] }} rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between gap-2 relative z-10 mb-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ $c['label'] }}</span>
                    <div class="shrink-0 w-8 h-8 rounded-full {{ $c['iconBg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['iconPath'] }}" />
                        </svg>
                    </div>
                </div>
                <div class="relative z-10 flex items-baseline gap-2">
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ $c['value'] }}</h3>
                    <span class="text-xs font-bold text-slate-400 uppercase">Pesan</span>
                </div>
                <p class="relative z-10 mt-3 pt-3 border-t border-gray-100 dark:border-white/5 text-[11px] leading-snug text-slate-400">{{ $c['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Tabs & Followup Priority Table --}}
    <div class="bg-white dark:bg-[#1e2336] rounded-2xl border border-gray-100 dark:border-white/5 overflow-hidden shadow-sm">
        {{-- Table Toolbar with Filter Tabs and Real-time Badge --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-[#151928] flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                {{-- Semua Antrean --}}
                <button type="button" wire:click="setTab('all')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ $activeTab === 'all' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/20' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' }}">
                    Semua Antrean
                </button>

                {{-- Urgent (>7H) --}}
                <button type="button" wire:click="setTab('urgent')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all inline-flex items-center gap-1.5 {{ $activeTab === 'urgent' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-slate-400 hover:text-rose-300 hover:bg-rose-500/5' }}">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Urgent (&gt;7H)</span>
                </button>

                {{-- Warning (>3H) --}}
                <button type="button" wire:click="setTab('warning')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all inline-flex items-center gap-1.5 {{ $activeTab === 'warning' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'text-slate-400 hover:text-amber-300 hover:bg-amber-500/5' }}">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Warning (&gt;3H)</span>
                </button>

                {{-- Info (>1H) --}}
                <button type="button" wire:click="setTab('info')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all inline-flex items-center gap-1.5 {{ $activeTab === 'info' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'text-slate-400 hover:text-blue-300 hover:bg-blue-500/5' }}">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Info (&gt;1H)</span>
                </button>
            </div>
            
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Data Real-time</span>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-[#151928] text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-gray-100 dark:border-white/5">
                        <th class="px-6 py-4">Info Pelanggan</th>
                        <th class="px-6 py-4">Status Chat</th>
                        <th class="px-6 py-4">Interaksi Terakhir</th>
                        <th class="px-6 py-4">Response Gap</th>
                        <th class="px-6 py-4">Prioritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse ($contacts as $contact)
                        @php
                            $custDate = $contact->last_contact_from_customers ? Carbon::parse($contact->last_contact_from_customers) : null;
                            $compDate = $contact->last_contacted_from_company ? Carbon::parse($contact->last_contacted_from_company) : null;
                            
                            $isWaiting = $custDate && (!$compDate || $custDate->gt($compDate));
                            $gapDays = $isWaiting ? round($custDate->diffInHours(now()) / 24) : 0;
                            
                            $badgeColor = 'slate';
                            $label = 'Normal';
                            if ($gapDays >= 7) { 
                                $badgeColor = 'rose'; 
                                $label = 'URGENT'; 
                            } elseif ($gapDays >= 3) { 
                                $badgeColor = 'amber'; 
                                $label = 'WARNING'; 
                            } elseif ($gapDays >= 1) { 
                                $badgeColor = 'blue'; 
                                $label = 'INFO'; 
                            }
                        @endphp
                        <tr wire:key="cs-row-{{ $contact->id }}" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                            {{-- Info Pelanggan --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/15 to-blue-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-black text-xs shrink-0">
                                        {{ substr($contact->first_name ?: '?', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight truncate">
                                            {{ $contact->first_name }} {{ $contact->last_name }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-400 font-mono tracking-tight mt-0.5">
                                            {{ $contact->phone_number }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Chat --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-500/10 text-slate-300 border border-slate-500/20">
                                    {{ $contact->status_chat ?: 'New Member' }}
                                </span>
                            </td>

                            {{-- Interaksi Terakhir --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                        <span class="text-slate-400 text-[11px]">Cust:</span>
                                        <span class="font-semibold">{{ $custDate ? $custDate->translatedFormat('d M, H:i') : '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 shrink-0"></span>
                                        <span class="text-slate-500 text-[11px]">You:</span>
                                        <span class="font-medium">{{ $compDate ? $compDate->translatedFormat('d M, H:i') : '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Response Gap --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($badgeColor === 'rose')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 font-black text-xs">
                                        {{ $gapDays }} <span class="text-[10px] font-bold uppercase">Hari</span>
                                    </span>
                                @elseif($badgeColor === 'amber')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 font-black text-xs">
                                        {{ $gapDays }} <span class="text-[10px] font-bold uppercase">Hari</span>
                                    </span>
                                @elseif($badgeColor === 'blue')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/20 font-black text-xs">
                                        {{ $gapDays }} <span class="text-[10px] font-bold uppercase">Hari</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-500/10 text-slate-400 border border-slate-500/20 font-bold text-xs">
                                        {{ $gapDays }} <span class="text-[10px] font-bold uppercase">Hari</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Prioritas --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if($badgeColor === 'rose')
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                        </span>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-400">{{ $label }}</span>
                                    @elseif($badgeColor === 'amber')
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-400">{{ $label }}</span>
                                    @elseif($badgeColor === 'blue')
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                        </span>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-400">{{ $label }}</span>
                                    @else
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-500"></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Semua Beres! Tidak ada antrean followup</p>
                                    <p class="text-[11px] text-slate-500">Semua respon pelanggan telah tertangani tepat waktu.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($contacts->hasPages())
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-[#151928] border-t border-gray-100 dark:border-white/5">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>
