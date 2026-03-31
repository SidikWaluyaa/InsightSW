<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Ringkasan Performa
            </h2>
        </div>
    </x-slot>

    <div class="space-y-6 relative" wire:poll.10s x-cloak>
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-3 md:p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
            <h3 class="font-bold text-[15px] md:text-lg text-slate-800 dark:text-gray-200 tracking-tight">Pilih Periode:</h3>
            <div class="flex items-center gap-3 relative">
                <div wire:loading wire:target="selectedMonth" class="absolute -left-10 top-1/2 -translate-y-1/2">
                    <div class="w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <input type="month" wire:model.live="selectedMonth" wire:key="dashboard-month-filter"
                    class="rounded-lg md:rounded-xl text-sm md:text-base border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 font-medium">
            </div>
        </div>
        {{-- Loading Overlay --}}
        <div wire:loading wire:target="selectedMonth" class="absolute inset-0 z-[60] bg-gray-50/50 dark:bg-gray-950/50 backdrop-blur-[2px] rounded-3xl flex items-center justify-center transition-all">
            <div class="flex flex-col items-center gap-3 p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Memuat Data...</span>
            </div>
        </div>
        
        <div wire:loading.class="opacity-25 transition-opacity" wire:target="selectedMonth" class="space-y-8">
        {{-- KPI Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- ROAS Card --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group">
                <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" /></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4" x-data="{ open: false }">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">ROAS Saat Ini</span>
                            <div class="relative flex items-center">
                                <button @mouseenter="open = true" @mouseleave="open = false" class="text-slate-300 dark:text-gray-600 hover:text-emerald-500 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                </button>
                                <div x-show="open" x-transition.opacity
                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48 p-2 bg-slate-800 dark:bg-gray-800 text-[10px] text-white rounded-lg shadow-xl z-[70] pointer-events-none text-center leading-tight font-normal lowercase first-letter:uppercase">
                                    <span class="font-bold text-emerald-400 font-bold uppercase">Rumus:</span><br>{{ $this->getFormula('roas') }}
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-slate-800 dark:border-b-gray-800"></div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $this->kpis['roas_color'] === 'green' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($this->kpis['roas_color'] === 'yellow' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400') }}">
                            {{ round(($this->kpis['roas'] / max(0.1, $this->kpis['target_roas'])) * 100) }}% Target
                        </span>
                    </div>
                    <h3 class="text-2xl xl:text-3xl font-black text-slate-800 dark:text-white leading-none">{{ $this->kpis['roas'] }}x</h3>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-gray-400">Target: {{ $this->kpis['target_roas'] }}x</span>
                            <span class="font-bold text-slate-700 dark:text-gray-200">{{ round(($this->kpis['roas'] / max(0.1, $this->kpis['target_roas'])) * 100) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $this->kpis['roas_color'] === 'green' ? 'bg-emerald-500' : ($this->kpis['roas_color'] === 'yellow' ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                style="width: {{ min(100, ($this->kpis['roas'] / max(0.1, $this->kpis['target_roas'])) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget Card --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group">
                <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4" x-data="{ open: false }">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Anggaran Terpakai</span>
                            <div class="relative flex items-center">
                                <button @mouseenter="open = true" @mouseleave="open = false" class="text-slate-300 dark:text-gray-600 hover:text-emerald-500 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                </button>
                                <div x-show="open" x-transition.opacity
                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48 p-2 bg-slate-800 dark:bg-gray-800 text-[10px] text-white rounded-lg shadow-xl z-[70] pointer-events-none text-center leading-tight font-normal lowercase first-letter:uppercase">
                                    <span class="font-bold text-emerald-400 font-bold uppercase">Rumus:</span><br>{{ $this->getFormula('remaining_budget') }}
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-slate-800 dark:border-b-gray-800"></div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $this->kpis['budget_color'] === 'green' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($this->kpis['budget_color'] === 'yellow' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400') }}">
                            {{ $this->kpis['budget_color'] === 'green' ? 'Aman' : ($this->kpis['budget_color'] === 'yellow' ? 'Peringatan' : 'Melebihi Batas') }}
                        </span>
                    </div>
                    <h3 class="text-2xl xl:text-3xl font-black text-slate-800 dark:text-white leading-none truncate">{{ $this->formatCurrency($this->kpis['total_spent']) }}</h3>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-gray-400">Total: {{ $this->formatCurrency($this->kpis['effective_budget']) }}</span>
                            <span class="font-bold text-slate-700 dark:text-gray-200">{{ $this->kpis['budget_used_percentage'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $this->kpis['budget_color'] === 'green' ? 'bg-emerald-500' : ($this->kpis['budget_color'] === 'yellow' ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                style="width: {{ min(100, $this->kpis['budget_used_percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue Card --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group">
                <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4" x-data="{ open: false }">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Total Omset</span>
                            <div class="relative flex items-center">
                                <button @mouseenter="open = true" @mouseleave="open = false" class="text-slate-300 dark:text-gray-600 hover:text-emerald-500 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                </button>
                                <div x-show="open" x-transition.opacity
                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48 p-2 bg-slate-800 dark:bg-gray-800 text-[10px] text-white rounded-lg shadow-xl z-[70] pointer-events-none text-center leading-tight font-normal lowercase first-letter:uppercase">
                                    <span class="font-bold text-emerald-400 font-bold uppercase">Rumus:</span><br>Penjumlahan omset dari laporan harian
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-slate-800 dark:border-b-gray-800"></div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $this->kpis['revenue_color'] === 'green' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($this->kpis['revenue_color'] === 'yellow' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400') }}">
                            {{ $this->kpis['revenue_percentage'] }}% Target
                        </span>
                    </div>
                    <h3 class="text-2xl xl:text-3xl font-black text-slate-800 dark:text-white leading-none truncate">{{ $this->formatCurrency($this->kpis['total_revenue']) }}</h3>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-gray-400">Target: {{ $this->formatCurrency($this->kpis['target_revenue']) }}</span>
                            <span class="font-bold text-slate-700 dark:text-gray-200">{{ round($this->kpis['revenue_percentage']) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $this->kpis['revenue_color'] === 'green' ? 'bg-emerald-500' : ($this->kpis['revenue_color'] === 'yellow' ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                style="width: {{ min(100, $this->kpis['revenue_percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chat Card --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 sm:p-5 lg:p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group">
                <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-violet-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4" x-data="{ open: false }">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Chat Konsul</span>
                            <div class="relative flex items-center">
                                <button @mouseenter="open = true" @mouseleave="open = false" class="text-slate-300 dark:text-gray-600 hover:text-emerald-500 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                </button>
                                <div x-show="open" x-transition.opacity
                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-48 p-2 bg-slate-800 dark:bg-gray-800 text-[10px] text-white rounded-lg shadow-xl z-[70] pointer-events-none text-center leading-tight font-normal lowercase first-letter:uppercase">
                                    <span class="font-bold text-emerald-400 font-bold uppercase">Rumus:</span><br>Penjumlahan chat konsul dari laporan harian
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-slate-800 dark:border-b-gray-800"></div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $this->kpis['chat_color'] === 'green' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($this->kpis['chat_color'] === 'yellow' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400') }}">
                            {{ $this->kpis['chat_percentage'] }}% Target
                        </span>
                    </div>
                    <h3 class="text-2xl xl:text-3xl font-black text-slate-800 dark:text-white leading-none">{{ $this->kpis['total_chat_consul'] }}</h3>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-gray-400">Target: {{ $this->kpis['target_chat_consul'] }} chat</span>
                            <span class="font-bold text-slate-700 dark:text-gray-200">{{ round($this->kpis['chat_percentage']) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $this->kpis['chat_color'] === 'green' ? 'bg-emerald-500' : ($this->kpis['chat_color'] === 'yellow' ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                style="width: {{ min(100, $this->kpis['chat_percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calendar Tracker Widget --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 dark:from-gray-900 dark:to-gray-800 rounded-3xl p-4 sm:p-5 lg:p-6 shadow-lg border border-slate-700 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-white backdrop-blur-md">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-white font-black text-lg lg:text-xl tracking-tight">Status Kalender Kerja</h3>
                        <p class="text-slate-300 text-sm mt-0.5">Rincian hari efektif berjalan bulan ini</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 flex-1 w-full md:w-auto md:ml-12 border-t md:border-t-0 md:border-l border-white/10 pt-4 md:pt-0 md:pl-8">
                    <div>
                        <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Hari Kerja</p>
                        <p class="text-white font-black text-xl xl:text-2xl">{{ $this->kpis['working_days'] }} <span class="text-xs font-medium text-slate-300">/hr</span></p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Sisa Hari</p>
                        <p class="text-white font-black text-xl xl:text-2xl">{{ $this->kpis['remaining_days'] }} <span class="text-xs font-medium text-slate-300">/hr</span></p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1 border-b border-dashed border-slate-600 pb-0.5 inline-block cursor-help" title="Sisa hari dalam blok minggu saat ini (hitung 1-7, 8-14 dst)">Sisa Mg Ini</p>
                        <p class="text-emerald-400 font-black text-xl xl:text-2xl">{{ $this->kpis['remaining_days_in_week'] }} <span class="text-xs font-medium text-emerald-500/50">/hr</span></p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Libur</p>
                        <p class="text-rose-400 font-black text-xl xl:text-2xl">{{ $this->kpis['total_holidays'] }} <span class="text-xs font-medium text-rose-500/50">/hr</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Metrics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            <x-metric-item label="Sisa Anggaran" :value="$this->formatCurrency($this->kpis['remaining_budget'])" icon="💰" :formula="$this->getFormula('remaining_budget')" />
            <x-metric-item label="Target Anggaran Harian" :value="$this->formatCurrency($this->kpis['daily_budget_target'])" icon="🎯" :formula="$this->getFormula('daily_budget_target')" />
            <x-metric-item label="Sisa Sasaran Omset" :value="$this->formatCurrency($this->kpis['remaining_revenue'])" icon="📈" :formula="$this->getFormula('remaining_revenue')" />
            <x-metric-item label="Greeting Rate" :value="$this->formatPercentage($this->kpis['greeting_rate'])" icon="💬" :formula="$this->getFormula('greeting_rate')" />
            <x-metric-item label="Biaya per Chat Konsul" :value="$this->formatCurrency($this->kpis['cost_per_chat_consul'])" icon="🚀" :formula="$this->getFormula('cost_per_chat_consul')" />
        </div>

        {{-- Smart Alerts --}}
        @if($this->kpis['roas'] > 0)
        <div class="space-y-3">
            @if($this->kpis['roas_color'] === 'red')
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20">
                    <span class="text-rose-500">⚠️</span>
                    <span class="text-sm text-rose-700 dark:text-rose-400 font-medium">Peringatan ROAS: ROAS saat ini ({{ number_format($this->kpis['roas'], 2) }}x) di bawah target ({{ number_format($this->kpis['target_roas'], 2) }}x). Pertimbangkan optimasi biaya iklan.</span>
                </div>
            @endif
            @if($this->kpis['greeting_rate'] < 50)
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                    <span class="text-amber-500">💬</span>
                    <span class="text-sm text-amber-700 dark:text-amber-400 font-medium">Greeting Rate {{ number_format($this->kpis['greeting_rate'], 1) }}% — di bawah 50%. Tinjau kualitas penanganan chat.</span>
                </div>
            @endif
        </div>
        @endif

        {{-- Main Table Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Data Performa Harian
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Anggaran</th>
                            <th class="px-6 py-4 text-right">Terpakai</th>
                            <th class="px-6 py-4 text-right">Omset</th>
                            <th class="px-6 py-4 text-center">ROAS</th>
                            <th class="px-6 py-4 text-center">Chat Masuk</th>
                            <th class="px-6 py-4 text-center">Chat Konsul</th>
                            <th class="px-6 py-4 text-center">Greeting Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($this->dailyReports as $report)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-gray-300">
                                    {{ $report->date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-gray-400">
                                    {{ $this->formatCurrency($report->budgeting) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-gray-100 font-semibold text-right">
                                    {{ $this->formatCurrency($report->spent) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-emerald-600 dark:text-emerald-400 font-bold text-right">
                                    {{ $this->formatCurrency($report->revenue) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $this->getRoasIndicator($report->spent > 0 ? $report->revenue / $report->spent : 0, $this->kpis['target_roas']) === 'green' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                        {{ number_format($report->spent > 0 ? $report->revenue / $report->spent : 0, 2) }}x
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-gray-400 text-center">
                                    {{ $report->chat_in }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-gray-200 text-center">
                                    {{ $report->chat_consul }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-12 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-violet-500" style="width: {{ $report->chat_in > 0 ? ($report->chat_consul/$report->chat_in)*100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-gray-400">
                                            {{ $report->chat_in > 0 ? round(($report->chat_consul/$report->chat_in)*100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p class="text-slate-400 dark:text-gray-500 text-sm italic">Belum ada data rekaman untuk bulan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
