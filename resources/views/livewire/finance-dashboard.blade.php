<div class="space-y-8" 
     wire:poll.10s="checkSync"
     x-data="{ 
        seconds: 0,
        lastSync: @entangle('lastSyncTimestamp'),
        isSyncing: @entangle('isSyncing'),
        serverTime: {{ time() }},
        browserTime: Math.floor(Date.now() / 1000),
        get drift() { return this.serverTime - this.browserTime; },
        init() {
            setInterval(() => {
                if (this.lastSync) {
                    let now = Math.floor(Date.now() / 1000) + this.drift;
                    this.seconds = Math.max(0, 60 - (now - this.lastSync));
                }
            }, 1000);
        }
     }">
    {{-- Header Section --}}
    <div class="space-y-4">
        <x-ui.page-header title="Financial Dashboard" subtitle="Tagihan, pembayaran, dan piutang SPK dari sinkron sistem pusat (60 hari bergulir)." badge="Live">
            <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="now()->toDateString()" :presets="$this->datePresets()" refresh="syncPusat" refresh-label="Sync Pusat" />
        </x-ui.page-header>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <x-ui.range-info :start="$startDate" :end="$endDate">
                <template x-if="isSyncing"><span class="font-semibold text-indigo-500 animate-pulse">Sinkronisasi…</span></template>
                <template x-if="!isSyncing && seconds > 0"><span>Pembaruan otomatis dalam <span x-text="seconds" class="font-mono text-indigo-500"></span> detik</span></template>
            </x-ui.range-info>

            {{-- Ekspor mengikuti filter yang sedang aktif --}}
            <div class="flex items-center gap-2">
                <x-ui.btn variant="secondary" icon="document" wire:click="exportExcel" target="exportExcel">Excel</x-ui.btn>
                <x-ui.btn variant="secondary" icon="document" :href="route('finance.export-pdf', ['startDate' => $startDate, 'endDate' => $endDate, 'search' => $search, 'statusFilter' => $statusFilter])" x-data x-on:click.prevent="window.open($el.href, '_blank')">PDF</x-ui.btn>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">{{ $trx->customer_name }}</span>
                                    <span class="text-[9px] font-medium text-slate-400 font-mono">{{ $trx->customer_phone ?: '-' }}</span>
                                </div>
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
