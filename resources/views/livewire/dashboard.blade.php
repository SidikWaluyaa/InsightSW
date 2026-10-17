<div>
    <div class="space-y-6 relative" wire:poll.60s="checkSync" x-cloak>
        <x-ui.page-header title="Ringkasan Performa" subtitle="Omset, SPK, efektivitas iklan, dan corong chat pada rentang terpilih. Rincian 31 data ada di menu Data Manajemen SW.">
            <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" refresh="syncAll" refresh-label="Tarik Data Live" />
        </x-ui.page-header>

        <x-ui.range-info :start="$startDate" :end="$endDate" :updated="$lastSyncTime" />

        {{-- Sequential Sync Overlay --}}
        <div x-data="{ 
                show: @entangle('isSyncing'),
                progress: 0,
                message: @entangle('syncMessage')
             }"
             x-show="show"
             x-on:sync-started.window="progress = 0"
             x-on:chunk-completed.window="progress = $event.detail.progress; $wire.syncNextChunk()"
             x-on:sync-finished.window="show = false"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div class="bg-white dark:bg-gray-900 w-full max-w-md p-8 rounded-3xl shadow-2xl border border-white/20 relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 bg-emerald-500 transition-all duration-500" :style="{ width: progress + '%' }"></div>
                
                <div class="flex flex-col items-center gap-6 text-center">
                    <div class="relative">
                        <div class="w-20 h-20 border-4 border-emerald-100 dark:border-emerald-500/10 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="progress + '%'"></span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h4 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Menyinkronkan Data</h4>
                        <p class="text-sm text-slate-500 dark:text-gray-400 font-medium leading-relaxed" x-text="message"></p>
                    </div>

                    <div class="w-full bg-gray-100 dark:bg-gray-800 h-3 rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]" :style="{ width: progress + '%' }"></div>
                    </div>

                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">Mohon jangan tutup halaman ini</p>
                </div>
            </div>
        </div>

        {{-- Loading Overlay --}}
        <div wire:loading wire:target="setDateRange" class="absolute inset-0 z-[60] bg-gray-50/50 dark:bg-gray-950/50 backdrop-blur-[2px] rounded-3xl flex items-center justify-center transition-all">
            <div class="flex flex-col items-center gap-3 p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-sm font-bold text-slate-600 dark:text-gray-300">Memuat Data...</span>
            </div>
        </div>
        
        <div wire:loading.class="opacity-25 transition-opacity" wire:target="setDateRange" class="space-y-8">
        {{-- Executive Overview (PRD): 4 kartu utama, corong chat, tren, SPK per jenis. 31 data Iklan & CS ada di halaman Data Manajemen SW. --}}
        <livewire:executive-overview :start="$startDate" :end="$endDate" />

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
                                <td class="px-6 py-3">
                                    <div class="relative group/input max-w-[150px]" 
                                        x-data="{ 
                                            value: '{{ number_format($report->budgeting, 0, ',', '.') }}',
                                            format() {
                                                let raw = this.value.replace(/[^0-9]/g, '');
                                                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                            }
                                        }">
                                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 group-focus-within/input:text-emerald-500 transition-colors">
                                            <span class="text-xs font-bold">Rp</span>
                                        </div>
                                        <input type="text" 
                                            x-model="value"
                                            x-on:input="format()"
                                            onblur="@this.updateBudget({{ $report->id }}, this.value)"
                                            onkeydown="if(event.key==='Enter') this.blur()"
                                            class="w-full pl-8 pr-3 py-1.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-400 outline-none transition-all hover:border-gray-300 dark:hover:border-gray-600 shadow-inner"
                                            placeholder="0" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-gray-100 font-semibold text-right">
                                    {{ $this->formatCurrency($report->spent) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-emerald-600 dark:text-emerald-400 font-bold text-right">
                                    {{ $this->formatCurrency($report->revenue) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
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
                                        <p class="text-slate-400 dark:text-gray-500 text-sm italic">Belum ada data rekaman untuk rentang ini.</p>
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
</div>
