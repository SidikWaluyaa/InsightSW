<div class="space-y-6" x-data="{ syncing: @entangle('isSyncing') }">
    <x-ui.page-header title="Inventori & Analitik" subtitle="Stok material, valuasi aset, dan aktivitas gudang pada rentang terpilih.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="now()->toDateString()" :presets="$this->datePresets()" refresh="syncAll" refresh-label="Sinkron Data" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate" :updated="$lastSync" />

    {{-- PRIMARY INVENTORY WIDGETS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Asset Valuation --}}
        <div class="group relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 transition-all duration-500 overflow-hidden shadow-sm">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 group-hover:scale-125 transition-all duration-700 text-emerald-400">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-[0.3em] mb-4">Total Nilai Barang (Modal)</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none tracking-tight">{{ $this->formatCurrency($this->assetValuation['grand_total']) }}</h3>
                </div>
                <p class="mt-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Terikat pada {{ $this->totalStock }} unit barang aktif di seluruh rak.</p>
            </div>
        </div>

        {{-- Stock Health --}}
        <div class="group relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 transition-all duration-500 overflow-hidden shadow-sm">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-[0.3em]">Skor Kesehatan Stok</p>
                    <span class="text-[10px] font-black px-3 py-1 rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/20">GRADE {{ $this->stockHealthScore['grade'] }}</span>
                </div>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-4xl font-black text-slate-800 dark:text-white leading-none">{{ $this->stockHealthScore['score'] }}%</h3>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)] transition-all duration-1000" style="width: {{ $this->stockHealthScore['score'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[9px] font-bold uppercase tracking-tighter">
                        <span class="text-emerald-500">{{ $this->stockHealthScore['healthy'] }} AMAN</span>
                        <span class="text-amber-500">{{ $this->stockHealthScore['low'] }} LOW</span>
                        <span class="text-rose-500">{{ $this->stockHealthScore['out'] }} HABIS</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Physical Stock --}}
        <div class="group relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 transition-all duration-500 overflow-hidden shadow-sm">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 group-hover:scale-125 transition-all duration-700 text-blue-400">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.97 4.44c-.31.17-.69.17-1 0l-7.97-4.44c-.32-.17-.53-.5-.53-.88v-9c0-.38.21-.71.53-.88l7.97-4.44c.31-.17.69-.17 1 0l7.97 4.44c.32.17.53.5.53.88v9z"/></svg>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-[0.3em] mb-4">Total Stok Fisik</p>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-4xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($this->totalStock) }}</h3>
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Unit</span>
                </div>
                <p class="mt-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Tersebar di berbagai sub-kategori material.</p>
            </div>
        </div>

        {{-- Out of Stock Alert --}}
        <div class="group relative bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 transition-all duration-500 overflow-hidden shadow-sm">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-[0.3em]">Peringatan Stok Habis</p>
                    @if($this->stockHealthScore['out'] > 0)
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></div>
                    @endif
                </div>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-4xl font-black text-rose-500 leading-none tracking-tighter">{{ $this->stockHealthScore['out'] }}</h3>
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Barang</span>
                </div>
                <div class="mt-6 flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full bg-rose-500"></div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">+ {{ $this->stockHealthScore['low'] }} ITEM MENDEKATI BATAS MINIMUM</span>
                </div>
            </div>
        </div>
    </div>

    {{-- OPERATIONAL METRICS (API SYNCED) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $opStats = [
                ['label' => 'Sepatu di Rak', 'value' => $this->warehouseStats['total_sepatu_dirak'], 'sub' => 'SIAP PICKUP', 'color' => 'indigo'],
                ['label' => 'Selesai Periode', 'value' => $this->warehouseStats['total_sepatu_finish_periode'], 'sub' => 'VERIFIED QC', 'color' => 'emerald'],
                ['label' => 'Masuk Periode', 'value' => $this->warehouseStats['total_sepatu_diterima_periode'], 'sub' => 'INFLOW UNIT', 'color' => 'amber'],
                ['label' => 'Total QC Lolos', 'value' => $this->warehouseStats['total_spk_print'], 'sub' => 'QUALITY PASS', 'color' => 'slate'],
            ];
        @endphp

        @foreach($opStats as $stat)
        <div wire:key="stat-{{ $stat['label'] ?? md5(json_encode($stat)) }}" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between group hover:border-gray-200 dark:hover:border-gray-700 shadow-sm transition-all">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($stat['value']) }}</span>
                    <span class="text-[9px] font-bold text-{{ $stat['color'] }}-500/70 uppercase">{{ $stat['sub'] }}</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-500/10 flex items-center justify-center text-{{ $stat['color'] }}-500 group-hover:rotate-12 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
        @endforeach
    </div>

    {{-- INVENTORY ANALYSIS TABLE --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Daftar Inventaris Lengkap</h3>
                <p class="text-[10px] text-slate-500 uppercase font-black tracking-[0.2em] mt-1">Audit Stok & Valuasi Aset</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative min-w-[280px] group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="CARI MATERIAL..."
                        class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl pl-12 pr-4 py-3 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all placeholder:text-slate-400">
                </div>

                <select wire:model.live="statusFilter" class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                    <option value="all">SEMUA STATUS</option>
                    <option value="In Stock">AMAN</option>
                    <option value="Low Stock">LOW STOCK</option>
                    <option value="Out of Stock">HABIS</option>
                </select>

                <select wire:model.live="subCategoryFilter" class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                    <option value="all">SEMUA KATEGORI</option>
                    @foreach($this->subCategories as $sc)
                        <option wire:key="sc-{{ $sc }}" value="{{ $sc }}">{{ strtoupper($sc) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-[0.2em] font-black text-slate-500">
                        <th class="px-8 py-5">Nama Material</th>
                        <th class="px-8 py-5">Kategori</th>
                        <th class="px-8 py-5 text-center">Stok / Min</th>
                        <th class="px-8 py-5 text-right">Valuasi (Modal)</th>
                        <th class="px-8 py-5 text-center">Status Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-bold">
                    @forelse($this->allInventory as $item)
                    <tr wire:key="inv-{{ $item->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all group">
                        <td class="px-8 py-5 uppercase text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $item->name }}</td>
                        <td class="px-8 py-5">
                            <span class="px-2 py-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-[9px] text-slate-500 dark:text-gray-400 uppercase tracking-widest">{{ $item->sub_category ?: '-' }}</span>
                        </td>
                        <td class="px-8 py-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="{{ $item->current_stock <= $item->min_stock ? 'text-rose-500' : 'text-emerald-500' }}">{{ number_format($item->current_stock) }}</span>
                                <span class="text-slate-300 dark:text-gray-600">/</span>
                                <span class="text-slate-500 text-[10px]">{{ number_format($item->min_stock) }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ $this->formatCurrency($item->total_valuation) }}</td>
                        <td class="px-8 py-5 text-center">
                            @if($item->status === 'Out of Stock')
                                <span class="px-3 py-1 bg-rose-500/10 text-rose-500 rounded-full border border-rose-500/20 text-[9px] font-black uppercase">HABIS</span>
                            @elseif($item->current_stock <= $item->min_stock)
                                <span class="px-3 py-1 bg-amber-500/10 text-amber-500 rounded-full border border-amber-500/20 text-[9px] font-black uppercase">LOW</span>
                            @else
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 text-[9px] font-black uppercase">AMAN</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="text-xs font-black uppercase tracking-[0.3em]">Tidak ada data inventaris</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->allInventory->hasPages())
        <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
            {{ $this->allInventory->links() }}
        </div>
        @endif
    </div>

</div>
