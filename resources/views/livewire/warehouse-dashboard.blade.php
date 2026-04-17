<div>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-gray-100 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#22AF85]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Warehouse Intelligence
                </h2>
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400 mt-1">Supply Chain Analytics & Stock Health Scoring</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="syncAll" wire:loading.attr="disabled"
                    class="group relative px-6 py-2.5 bg-gradient-to-r from-[#22AF85] to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all overflow-hidden disabled:opacity-50 flex items-center gap-2">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    <svg wire:loading.class="animate-spin" class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span class="relative z-10 uppercase tracking-widest" wire:loading.remove wire:target="syncAll">Sync Realtime</span>
                    <span class="relative z-10 uppercase tracking-widest" wire:loading wire:target="syncAll">Menarik Data...</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">

        {{-- ROW 1: Primary KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Asset Valuation --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-[#22AF85]/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Asset Valuation</p>
                    <h3 class="text-xl font-black text-[#22AF85]">{{ $this->formatCurrency($this->assetValuation['grand_total']) }}</h3>
                    <div class="mt-3 text-[10px] font-bold text-slate-400">
                        Modal terikat pada {{ $this->totalItems }} SKU gudang fisik.
                    </div>
                </div>
            </div>

            {{-- Stock Health Score --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Stock Health Score</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-xl font-black {{ $this->stockHealthScore['score'] >= 75 ? 'text-emerald-500' : ($this->stockHealthScore['score'] >= 50 ? 'text-amber-500' : 'text-rose-500') }}">{{ $this->stockHealthScore['score'] }}%</h3>
                        <span class="text-xs font-black px-2 py-0.5 rounded-lg {{ $this->stockHealthScore['score'] >= 75 ? 'bg-emerald-500/10 text-emerald-500' : ($this->stockHealthScore['score'] >= 50 ? 'bg-amber-500/10 text-amber-500' : 'bg-rose-500/10 text-rose-500') }}">
                            GRADE {{ $this->stockHealthScore['grade'] }}
                        </span>
                    </div>
                    <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full transition-all duration-1000 {{ $this->stockHealthScore['score'] >= 75 ? 'bg-emerald-500' : ($this->stockHealthScore['score'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $this->stockHealthScore['score'] }}%"></div>
                    </div>
                    <div class="mt-2 text-[10px] font-bold text-slate-400">
                        {{ $this->stockHealthScore['healthy'] }} sehat • {{ $this->stockHealthScore['low'] }} peringatan • {{ $this->stockHealthScore['out'] }} habis
                    </div>
                </div>
            </div>

            {{-- Total Physical Stock --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Physical Stock</p>
                    <h3 class="text-xl font-black text-blue-600 dark:text-blue-400">{{ number_format($this->totalStock) }} <span class="text-xs text-slate-400 font-semibold">Unit</span></h3>
                    <div class="mt-3 text-[10px] font-bold text-slate-400">
                        Dari {{ $this->totalItems }} jenis material unik (SKU).
                    </div>
                </div>
            </div>

            {{-- Out of Stock --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Dead Stock Alert</p>
                            <h3 class="text-xl font-black {{ $this->outOfStockCount > 0 ? 'text-rose-600' : 'text-slate-800 dark:text-white' }}">{{ $this->outOfStockCount }} <span class="text-xs text-slate-400 font-semibold">Item Habis</span></h3>
                        </div>
                        @if($this->outOfStockCount > 0)
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                        @endif
                    </div>
                    <div class="mt-3 text-[10px] font-bold text-slate-400">
                        + {{ $this->lowStockCount }} item mendekati batas minimum.
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: Sub-Category Breakdown --}}
        <div class="grid grid-cols-1 gap-6">
            {{-- Sub-Category Breakdown --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="mb-6">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-white uppercase tracking-widest">Sub-Category Breakdown</h3>
                    <p class="text-[10px] text-slate-400 mt-1">Distribusi material per jenis bahan produksi.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($this->subCategoryBreakdown as $sub)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700/30">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#22AF85]/20 to-teal-500/10 flex items-center justify-center text-[#22AF85] text-sm font-black shrink-0">
                            {{ $sub->item_count }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black text-slate-700 dark:text-white uppercase truncate">{{ $sub->sub_category_label }}</p>
                            <p class="text-[10px] font-bold text-[#22AF85] mt-0.5">{{ $this->formatCurrency($sub->total_val) }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    @php $pctVal = $this->assetValuation['grand_total'] > 0 ? ($sub->total_val / $this->assetValuation['grand_total']) * 100 : 0; @endphp
                                    <div class="bg-[#22AF85] h-1.5 rounded-full" style="width: {{ round($pctVal) }}%"></div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 shrink-0">{{ round($pctVal, 1) }}%</span>
                            </div>
                            <p class="text-[9px] text-slate-400 mt-1">{{ number_format($sub->total_stock) }} unit • Avg @ {{ $this->formatCurrency($sub->avg_price) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ROW 3: Top 10 Highest Valuation Items --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-[#22AF85]/5 to-transparent">
                <h3 class="font-bold text-sm text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Top 10 — Material Tertinggi (High Asset Concentration)
                </h3>
                <p class="text-[10px] text-slate-400 mt-1">Material dengan modal terbesar terikat di inventaris. Klasifikasi ABC: pastikan item ini tidak pernah kehabisan stok.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400">
                            <th class="px-8 py-4 text-center">#</th>
                            <th class="px-8 py-4">Material</th>
                            <th class="px-8 py-4">Jenis</th>
                            <th class="px-8 py-4 text-center">Stok</th>
                            <th class="px-8 py-4 text-right">Harga Satuan</th>
                            <th class="px-8 py-4 text-right">Total Valuasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-bold">
                        @foreach($this->topValueItems as $idx => $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-8 py-4 text-center">
                                @if($idx < 3)
                                <span class="w-6 h-6 rounded-full {{ $idx === 0 ? 'bg-amber-500' : ($idx === 1 ? 'bg-slate-400' : 'bg-amber-700') }} text-white text-[9px] font-black inline-flex items-center justify-center">{{ $idx + 1 }}</span>
                                @else
                                <span class="text-slate-400">{{ $idx + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-slate-800 dark:text-white uppercase">{{ $item->name }}</td>
                            <td class="px-8 py-4"><span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-800 text-slate-500 rounded text-[9px]">{{ $item->sub_category ?: '-' }}</span></td>
                            <td class="px-8 py-4 text-center {{ $item->current_stock <= $item->min_stock ? 'text-rose-600' : 'text-emerald-600' }}">{{ $item->current_stock }} {{ $item->unit }}</td>
                            <td class="px-8 py-4 text-right text-slate-500">{{ $this->formatCurrency($item->unit_price) }}</td>
                            <td class="px-8 py-4 text-right text-[#22AF85] font-black">{{ $this->formatCurrency($item->total_valuation) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ROW 4: Full Inventory Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-indigo-50/30 dark:bg-indigo-900/10">
                <div>
                    <h3 class="font-bold text-sm text-slate-800 dark:text-white uppercase tracking-widest">Seluruh Inventaris Gudang</h3>
                    <p class="text-[10px] text-slate-400 mt-1">Daftar lengkap {{ $this->totalItems }} material beserta status dan valuasinya.</p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Status Filter --}}
                    <select wire:model.live="statusFilter" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-[#22AF85] outline-none">
                        <option value="all">Semua Status</option>
                        <option value="In Stock">Aman</option>
                        <option value="Low Stock">Mendekati Habis</option>
                        <option value="Out of Stock">Habis</option>
                    </select>

                    {{-- Sub-Category Filter --}}
                    <select wire:model.live="subCategoryFilter" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-[#22AF85] outline-none">
                        <option value="all">Semua Jenis</option>
                        @foreach($this->subCategories as $sc)
                            <option value="{{ $sc }}">{{ $sc }}</option>
                        @endforeach
                    </select>

                    {{-- Search --}}
                    <div class="relative min-w-[250px]">
                        <input wire:model.live.debounce.300ms="search" type="text" 
                            placeholder="Cari material..."
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-[#22AF85] dark:text-gray-200 transition-all outline-none">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400">
                            <th class="px-6 py-4">Material</th>
                            <th class="px-6 py-4">Jenis</th>
                            <th class="px-6 py-4 text-center">Stok / Min</th>
                            <th class="px-6 py-4 text-right">Harga Satuan</th>
                            <th class="px-6 py-4 text-right">Valuasi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-bold">
                        @forelse($this->allInventory as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 uppercase text-slate-800 dark:text-white max-w-[200px] truncate">{{ $item->name }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-800 text-slate-500 rounded text-[9px]">{{ $item->sub_category ?: '-' }}</span></td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $item->current_stock <= $item->min_stock ? 'text-rose-600' : 'text-emerald-600' }}">{{ $item->current_stock }}</span>
                                <span class="text-slate-300 mx-1">/</span>
                                <span class="text-slate-400">{{ $item->min_stock }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500">{{ $this->formatCurrency($item->unit_price) }}</td>
                            <td class="px-6 py-4 text-right text-[#22AF85]">{{ $this->formatCurrency($item->total_valuation) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($item->status === 'Out of Stock')
                                    <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 rounded-full border border-rose-500/20 text-[9px]">HABIS</span>
                                @elseif($item->current_stock <= $item->min_stock)
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full border border-amber-500/20 text-[9px]">LOW</span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 text-[9px]">AMAN</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-gray-800 mb-3">
                                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                </div>
                                <p class="text-slate-500 font-bold text-xs">Belum ada data inventaris. Klik SYNC REALTIME.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800">
                {{ $this->allInventory->links() }}
            </div>
        </div>
    </div>
</div>
