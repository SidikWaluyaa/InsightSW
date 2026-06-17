<div>
    <div class="space-y-6 py-6" wire:init="loadData">
        @if (!$isLoaded)
            {{-- Skeleton Loaders --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 mb-8 animate-pulse">
                <div class="flex items-center gap-4 flex-1">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-gray-800"></div>
                    <div class="space-y-2">
                        <div class="h-6 w-48 bg-slate-100 dark:bg-gray-800 rounded"></div>
                        <div class="h-3 w-80 bg-slate-100 dark:bg-gray-800 rounded"></div>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-[200px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
                    <div class="w-[200px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
                </div>
            </div>
            
            <div class="h-20 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse mb-6"></div>
            <div class="h-96 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>

        @elseif ($errorMessage)
            {{-- Alert error --}}
            <div class="p-6 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-3xl flex items-start gap-4">
                <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-rose-800 dark:text-rose-400">Gagal Memuat Data</h3>
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Piutang: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            {{-- FIRST ROW: Header & KPI Cards --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 flex-1">
                    {{-- Icon Box --}}
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-3xl">
                        💳
                    </div>
                    <div>
                        <h2 class="font-black text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                            Piutang Before <span class="text-xs px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 ml-2 uppercase font-black tracking-widest animate-pulse">Live API</span>
                        </h2>
                        <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1">
                            DAFTAR INVOICE YANG BELUM LUNAS DARI SISTEM WORKSHOP — REALTIME SYNC
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 items-center w-full lg:w-auto">
                    {{-- Total Piutang Card --}}
                    <div class="w-full sm:w-[200px] bg-amber-50/50 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">TOTAL INVOICE</span>
                        <span class="text-4xl font-black text-amber-600 dark:text-amber-400 block my-1 font-mono">
                            {{ number_format($totalPiutangCount) }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Invoice Belum Lunas</span>
                    </div>
                    
                    {{-- Total Outstanding Card --}}
                    <div class="w-full sm:w-[240px] bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider block">TOTAL OUTSTANDING</span>
                        <span class="text-2xl font-black text-rose-600 dark:text-rose-400 block my-1 font-mono">
                            Rp {{ number_format($totalOutstandingSum, 0, ',', '.') }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Saldo Belum Terbayar</span>
                    </div>
                </div>
            </div>

            {{-- SECOND ROW: Search & Filter Box --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                    {{-- Search Box --}}
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none transition-all shadow-sm"
                            placeholder="Cari Invoice, SPK, Nama Pelanggan, atau Detail Sepatu...">
                    </div>

                    {{-- Right Filters & Actions --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        {{-- Status Filter --}}
                        <div class="relative">
                            <select wire:model.live="selectedStatus" 
                                class="w-full sm:w-48 px-4 py-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none transition-all cursor-pointer shadow-sm">
                                <option value="">Semua Status</option>
                                @foreach($allStatuses as $status)
                                    <option value="{{ $status }}">{{ strtoupper($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- PDF Download Link --}}
                        <a href="{{ route('finance-piutang-before.export-pdf', ['searchTerm' => $searchTerm, 'selectedStatus' => $selectedStatus]) }}" 
                            target="_blank"
                            class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-2xl text-center shadow-md shadow-amber-500/10 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span>📄</span>
                            <span>CETAK PDF</span>
                        </a>

                        @if($searchTerm || $selectedStatus)
                            {{-- Reset --}}
                            <button wire:click="resetFilters" 
                                class="p-3 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-bold rounded-2xl transition-all shadow-sm"
                                title="Reset Filter">
                                🔄
                            </button>
                        @endif

                        {{-- Refresh --}}
                        <button wire:click="refreshData" 
                            class="p-3 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800/40 dark:hover:bg-gray-850 text-slate-600 dark:text-gray-300 rounded-2xl border border-slate-200/50 dark:border-gray-800 transition-all active:scale-95 shadow-sm"
                            title="Refresh Data">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- THIRD ROW: Piutang Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">
                            DAFTAR PIUTANG OUTSTANDING
                        </h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">INVOICE YANG BELUM DILUNASI BESERTA DETAIL SEPATU DAN LAYANAN</p>
                    </div>

                    <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                        MENAMPILKAN: {{ count($paginatedItems) }} DARI {{ number_format($totalResults) }} DATA
                    </span>
                </div>

                {{-- Table container --}}
                <div class="overflow-x-auto border border-slate-100 dark:border-gray-800 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-900/50 border-b border-slate-100 dark:border-gray-800 uppercase text-[9px] font-black text-slate-400 dark:text-gray-500 tracking-wider">
                                <th class="px-5 py-4">INVOICE / SPK</th>
                                <th class="px-5 py-4">PELANGGAN</th>
                                <th class="px-5 py-4">DETAIL SEPATU</th>
                                <th class="px-5 py-4">LAYANAN / JASA</th>
                                <th class="px-5 py-4 text-right">OUTSTANDING</th>
                                <th class="px-5 py-4 text-center">STATUS</th>
                                <th class="px-5 py-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-[10px] font-bold text-slate-700 dark:text-gray-300">
                            @forelse($paginatedItems as $item)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/20 transition-colors align-top">
                                    {{-- INVOICE / SPK --}}
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-[11px]">{{ $item['invoice_number'] ?? '-' }}</span>
                                            @if(!empty($item['work_orders']))
                                                <div class="flex flex-col gap-0.5">
                                                    @foreach($item['work_orders'] as $wo)
                                                        <span class="text-[9px] text-slate-400 font-mono font-normal">{{ $wo['spk_number'] ?? '' }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    {{-- PELANGGAN --}}
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-white font-black uppercase">{{ $item['customer']['name'] ?? '-' }}</span>
                                            <span class="text-[9px] text-slate-400 font-normal font-mono mt-0.5">
                                                {{ $item['customer']['phone'] ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    {{-- DETAIL SEPATU --}}
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            @if(!empty($item['work_orders']))
                                                @foreach($item['work_orders'] as $wo)
                                                    <div class="flex flex-col">
                                                        <span class="text-slate-800 dark:text-white font-black uppercase">
                                                            {{ $wo['shoe']['brand'] ?? '' }} {{ $wo['shoe']['type'] ?? '' }}
                                                        </span>
                                                        <span class="text-[9px] text-slate-400 font-normal font-mono">
                                                            {{ !empty($wo['shoe']['color']) ? strtoupper($wo['shoe']['color']) : 'WARNA N/A' }} • SIZE {{ $wo['shoe']['size'] ?: '-' }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    {{-- LAYANAN / JASA --}}
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-[220px]">
                                            @php
                                                $allServices = [];
                                                if (!empty($item['work_orders'])) {
                                                    foreach ($item['work_orders'] as $wo) {
                                                        if (!empty($wo['services'])) {
                                                            foreach ($wo['services'] as $svc) {
                                                                $allServices[] = $svc['name'] ?? '';
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @forelse($allServices as $serviceName)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[8px] font-black tracking-wide bg-slate-800 dark:bg-slate-700 text-white uppercase whitespace-nowrap">
                                                    {{ $serviceName }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    
                                    {{-- OUTSTANDING --}}
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-col items-end gap-0.5">
                                            <span class="font-mono font-black text-amber-600 dark:text-amber-400 text-[11px]">
                                                Rp {{ number_format($item['financials']['remaining_balance'] ?? 0, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 font-normal">
                                                Lunas: Rp {{ number_format($item['financials']['paid_amount'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    {{-- STATUS --}}
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $statusText = strtoupper($item['financials']['status'] ?? '');
                                        @endphp
                                        @if($statusText === 'LUNAS')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">
                                                LUNAS
                                            </span>
                                        @elseif($statusText === 'BELUM BAYAR')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-rose-50 text-rose-700 border border-rose-200/50 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30">
                                                BELUM BAYAR
                                            </span>
                                        @elseif($statusText === 'BAYAR SEBAGIAN')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-amber-50 text-amber-700 border border-amber-200/50 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30">
                                                BAYAR SEBAGIAN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-slate-100 text-slate-600 border border-slate-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                                {{ $statusText ?: 'UNKNOWN' }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    {{-- AKSI --}}
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $waLink = $this->getWhatsAppLink($item);
                                        @endphp
                                        <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[9px] rounded-xl transition-all active:scale-95 shadow-md shadow-emerald-500/10 uppercase tracking-wider whitespace-nowrap {{ $waLink === '#' ? 'opacity-40 pointer-events-none' : '' }}">
                                            <span>💬</span>
                                            <span>WHATSAPP</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3 opacity-30">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-xs">Tidak ada data piutang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($totalPages > 1)
                    <div class="flex items-center justify-between mt-6">
                        <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                            HALAMAN {{ $currentPage }} DARI {{ $totalPages }}
                        </span>
                        <div class="flex items-center gap-1">
                            {{-- Previous --}}
                            @if($currentPage > 1)
                                <button wire:click="setPage({{ $currentPage - 1 }})" class="px-3 py-2 rounded-xl text-[10px] font-bold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-700 transition-all">
                                    ← PREV
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max(1, $currentPage - 2);
                                $end = min($totalPages, $currentPage + 2);
                            @endphp
                            @for($i = $start; $i <= $end; $i++)
                                <button wire:click="setPage({{ $i }})" 
                                    class="w-9 h-9 rounded-xl text-[10px] font-black transition-all {{ $i === $currentPage ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-700' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            {{-- Next --}}
                            @if($currentPage < $totalPages)
                                <button wire:click="setPage({{ $currentPage + 1 }})" class="px-3 py-2 rounded-xl text-[10px] font-bold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-700 transition-all">
                                    NEXT →
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
