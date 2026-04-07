<div class="p-4 sm:p-6 md:p-8 space-y-8 bg-slate-50 dark:bg-slate-950 min-h-screen transition-all duration-500 ease-in-out" 
     wire:poll.10s="checkSync"
     x-data="{ 
        seconds: 0,
        lastSync: @entangle('lastSyncTimestamp'),
        isSyncing: @entangle('isSyncing'),
        init() {
            setInterval(() => {
                if (this.lastSync) {
                    let now = Math.floor(Date.now() / 1000);
                    this.seconds = Math.max(0, 60 - (now - this.lastSync));
                }
            }, 1000);
        }
     }">
    {{-- Header Section --}}
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-xl shadow-indigo-500/20 ring-4 ring-white dark:ring-slate-900 overflow-hidden relative group">
                    <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div class="absolute inset-0 bg-white/20 group-hover:translate-y-full transition-transform duration-500"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase leading-none">Finance Center</h1>
                    <div class="flex items-center gap-3 mt-3">
                        <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-500 uppercase tracking-widest leading-none">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Live
                        </span>
                        
                        <div class="flex items-center gap-2 border-l border-slate-200 dark:border-slate-800 ml-2 pl-4">
                            <template x-if="isSyncing">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-3 w-3 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Sinkronisasi...</span>
                                </div>
                            </template>
                            <template x-if="!isSyncing && seconds > 0">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Auto-update: <span x-text="seconds" class="text-indigo-500 font-mono"></span>s</span>
                            </template>
                            <template x-if="!isSyncing && seconds <= 0">
                                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest animate-pulse">Menunggu Antrian...</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Primary Action --}}
            <div class="flex items-center gap-3">
                <button wire:click="syncPusat" wire:loading.attr="disabled"
                    class="group relative flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-300 active:scale-95 disabled:opacity-50 overflow-hidden">
                    <div class="flex items-center gap-2.5 relative z-10">
                        <svg wire:loading.remove wire:target="syncPusat" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <svg wire:loading wire:target="syncPusat" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <div class="flex flex-col items-start leading-none">
                            <span class="text-sm font-black uppercase tracking-tight">Sync Pusat</span>
                            <span class="text-[9px] font-bold text-indigo-200/60 uppercase tracking-widest mt-0.5">60 Days Rolling</span>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
            </div>
        </div>

        {{-- Unified Control Toolbar --}}
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-2 rounded-2xl shadow-sm flex flex-wrap items-center gap-2">
            {{-- Search --}}
            <div class="relative flex-grow md:max-w-xs group">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari SPK atau Nama..." 
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all dark:text-white placeholder:text-slate-400 font-medium">
            </div>

            <div class="h-8 w-px bg-slate-200 dark:bg-slate-800 hidden md:block"></div>

            {{-- Status Filter --}}
            <div class="flex items-center gap-2">
                <select wire:model.live="statusFilter" 
                    class="pl-3 pr-8 py-2.5 text-xs font-bold bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all dark:text-white cursor-pointer uppercase tracking-tight">
                    <option value="">Semua Status</option>
                    <option value="BB">Belum Bayar (BB)</option>
                    <option value="BL">Dp/Cicil (BL)</option>
                    <option value="L">Lunas (L)</option>
                </select>
            </div>

            <div class="h-8 w-px bg-slate-200 dark:bg-slate-800 hidden md:block"></div>

            {{-- Date Range --}}
            <div class="flex items-center bg-white dark:bg-slate-950 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-1.5 gap-1">
                <input type="date" wire:model.live.debounce.400ms="startDate" class="bg-transparent border-none text-xs focus:ring-0 dark:text-white font-black uppercase">
                <span class="text-slate-300 dark:text-slate-700 font-bold">/</span>
                <input type="date" wire:model.live.debounce.400ms="endDate" class="bg-transparent border-none text-xs focus:ring-0 dark:text-white font-black uppercase">
            </div>

            <div class="ml-auto hidden lg:flex items-center gap-2 px-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Toolbar Controls</span>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        {{-- Revenue Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-16 h-16 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Omset <small class="lowercase font-normal opacity-70">(nett)</small></p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">Rp {{ number_format($stats['total_net_revenue'], 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 font-mono tracking-tight">SPK: {{ $stats['total_count'] }}</span>
            </div>
        </div>

        {{-- Collection Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-16 h-16 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Terbayar</p>
            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-none">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</h3>
            <div class="mt-4 space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-slate-400 uppercase">Collection Rate</span>
                    <span class="text-emerald-500">{{ round($stats['collection_rate'], 1) }}%</span>
                </div>
                <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000" style="width: {{ $stats['collection_rate'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- NEW: Shipping Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-16 h-16 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Ongkir</p>
            <h3 class="text-2xl font-black text-amber-600 dark:text-amber-500 leading-none">Rp {{ number_format($stats['total_shipping'], 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-500 font-mono tracking-tight">Total Gabungan: Rp {{ number_format($stats['total_bill'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Debt Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-16 h-16 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Piutang (Hutang CS)</p>
            <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 leading-none">Rp {{ number_format($stats['total_remaining'], 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 font-mono tracking-tight">Rasio: {{ round(100 - $stats['collection_rate'], 1) }}%</span>
            </div>
        </div>

        {{-- Status Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-16 h-16 text-violet-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Sales Health</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ $stats['lunas_count'] }} <span class="text-sm font-medium text-slate-400">Lunas</span></h3>
            <div class="mt-4 flex items-center gap-2">
                <div class="flex -space-x-2">
                    <div class="w-6 h-6 rounded-full bg-indigo-500 border-2 border-white dark:border-slate-900"></div>
                    <div class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></div>
                    <div class="w-6 h-6 rounded-full bg-amber-500 border-2 border-white dark:border-slate-900"></div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 tracking-tighter">Mencapai Target</span>
            </div>
        </div>
    </div>

    {{-- Main Activity Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Aktivitas Transaksi</h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Total: {{ $transactions->total() }} record</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">SPK & Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Stat Pembayaran</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tagihan</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ongkir</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Terbayar</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-500 transition-colors tracking-tight">{{ $trx->spk_number }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                        {{ $trx->source_created_at ? \Carbon\Carbon::parse($trx->source_created_at)->format('d M Y, H:i') : '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                                {{ $trx->customer_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $isLunas = $trx->status_pembayaran === 'L';
                                    $isPartial = in_array($trx->status_pembayaran, ['C', 'BL', 'DP']);
                                    $colorClass = $isLunas ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : ($isPartial ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-rose-500/10 text-rose-500 border-rose-500/20');
                                    $labelText = $isLunas ? 'LUNAS' : ($isPartial ? 'DP/CICIL' : 'BLM BAYAR');
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter border {{ $colorClass }}">
                                    {{ $labelText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-700 dark:text-slate-300">
                                Rp {{ number_format($trx->total_bill - $trx->shipping_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-indigo-500">
                                Rp {{ number_format($trx->shipping_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-emerald-500">
                                Rp {{ number_format($trx->amount_paid, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-black text-rose-500">
                                Rp {{ number_format($trx->remaining_balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-slate-200 dark:text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                    <p class="text-slate-400 text-sm italic">Tidak ada data transaksi ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
