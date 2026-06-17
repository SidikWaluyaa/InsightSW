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
                    <div class="w-[180px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
                    <div class="w-[180px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
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
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Rak Sepatu: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            {{-- FIRST ROW: Header & KPI Cards in one unified design --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Green Icon Box -->
                    <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-3xl">
                        👟
                    </div>
                    <div>
                        <h2 class="font-black text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                            Sepatu di Rak <span class="text-slate-400 font-medium">(Semua Status)</span>
                        </h2>
                        <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1">
                            DAFTAR SEMUA SEPATU YANG TERSIMPAN DI RAK PENYIMPANAN BERDASARKAN DATA STORAGE ASSIGNMENTS
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 items-center w-full lg:w-auto">
                    <!-- Total Shoes Card -->
                    <div class="w-full sm:w-[200px] bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">TOTAL SEPATU DI RAK</span>
                        <span class="text-4xl font-black text-[#22AF85] block my-1 font-mono">
                            {{ number_format($totalShoes) }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Total Sepatu Tersimpan di Rak</span>
                    </div>
                    
                    <!-- Donation Recommendations Card -->
                    <div class="w-full sm:w-[200px] bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider block">REKOMENDASI DONASI (&gt; 3 BULAN)</span>
                        <span class="text-4xl font-black text-rose-600 dark:text-rose-400 block my-1 font-mono">
                            {{ number_format($donationCandidatesCount) }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Sepatu Siap Disalurkan Donasi</span>
                    </div>
                </div>
            </div>

            {{-- SECOND ROW: Search & Filter Box --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                    <!-- Search Box (Flex: 1) -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all shadow-sm"
                            placeholder="Cari SPK, Nama Pelanggan, Rak, atau Detail Sepatu...">
                    </div>

                    <!-- Right Filters & Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Status Filter -->
                        <div class="relative">
                            <select wire:model.live="selectedStatus" 
                                class="w-full sm:w-48 px-4 py-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all cursor-pointer shadow-sm">
                                <option value="">Semua Status SPK</option>
                                @foreach($allStatuses as $status)
                                    <option value="{{ $status }}">{{ strtoupper($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Donation Filter Toggle -->
                        <button wire:click="$set('showDonationOnly', {{ !$showDonationOnly ? 'true' : 'false' }})" 
                            class="px-4 py-3 rounded-2xl text-xs font-bold transition-all flex items-center justify-center gap-2 border shadow-sm {{ $showDonationOnly ? 'bg-rose-500 text-white border-rose-500 shadow-rose-500/10' : 'bg-slate-50 hover:bg-slate-100 dark:bg-gray-800/40 dark:hover:bg-gray-850 text-slate-600 dark:text-gray-300 border-slate-200/50 dark:border-gray-800' }}">
                            <span>🎁</span>
                            <span class="whitespace-nowrap">Donasi (&gt; 3 Bln)</span>
                        </button>

                        <!-- PDF Download Link -->
                        <a href="{{ route('warehouse-shoes-in-rack.export-pdf', ['searchTerm' => $searchTerm, 'selectedStatus' => $selectedStatus, 'showDonationOnly' => $showDonationOnly ? '1' : '0']) }}" 
                            target="_blank"
                            class="px-5 py-3 bg-[#22AF85] hover:bg-[#1f9c76] text-white font-black text-xs rounded-2xl text-center shadow-md shadow-emerald-500/10 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span>📄</span>
                            <span>CETAK PDF</span>
                        </a>

                        @if($searchTerm || $selectedStatus || $showDonationOnly)
                            <!-- Reset -->
                            <button wire:click="resetFilters" 
                                class="p-3 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-bold rounded-2xl transition-all shadow-sm"
                                title="Reset Filter">
                                🔄
                            </button>
                        @endif

                        <!-- Refresh -->
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

            {{-- THIRD ROW: Shoerack Logs List Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">
                            DAFTAR SYNC PENYIMPANAN SEPATU
                        </h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">DAFTAR SEPATU DI RAK BESERTA DURASI TERSIMPAN DAN REKOMENDASI DONASI</p>
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
                                <th class="px-5 py-4">SPK / ORDER</th>
                                <th class="px-5 py-4">PELANGGAN</th>
                                <th class="px-5 py-4">DETAIL SEPATU</th>
                                <th class="px-5 py-4 text-center">STATUS SPK</th>
                                <th class="px-5 py-4 text-center">POSISI RAK</th>
                                <th class="px-5 py-4">TANGGAL MASUK RAK</th>
                                <th class="px-5 py-4 text-center">DURASI TERSIMPAN</th>
                                <th class="px-5 py-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 uppercase font-bold text-slate-700 dark:text-gray-300 text-[10px]">
                            @forelse($paginatedItems as $item)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <!-- SPK / ORDER -->
                                    <td class="px-5 py-4 font-mono font-black text-slate-800 dark:text-white">
                                        {{ $item['spk_number'] ?? '-' }}
                                    </td>
                                    
                                    <!-- PELANGGAN -->
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-white font-black">{{ $item['customer']['name'] ?? '-' }}</span>
                                            <span class="text-[9px] text-slate-400 font-normal font-mono mt-0.5">
                                                {{ $item['customer']['phone'] ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- DETAIL SEPATU -->
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-white font-black">
                                                {{ $item['shoe']['brand'] ?? '' }} {{ $item['shoe']['type'] ?? '' }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 font-normal font-mono mt-0.5">
                                                {{ !empty($item['shoe']['color']) ? strtoupper($item['shoe']['color']) : 'WARNA N/A' }} • SIZE {{ $item['shoe']['size'] ?: '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- STATUS SPK -->
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $status = strtoupper($item['wo_status'] ?? '');
                                        @endphp
                                        @if($status === 'SELESAI')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">
                                                SELESAI
                                            </span>
                                        @elseif($status === 'PRODUCTION')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-indigo-50 text-indigo-700 border border-indigo-200/50 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-900/30">
                                                PRODUCTION
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-rose-50 text-rose-700 border border-rose-200/50 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30">
                                                {{ $status ?: 'UNKNOWN' }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- POSISI RAK -->
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-teal-50 text-teal-700 border border-teal-200/50 dark:bg-teal-950/20 dark:text-teal-400 dark:border-teal-900/30">
                                            📍 RAK: {{ $item['storage']['rack_code'] ?? '-' }}
                                        </span>
                                    </td>
                                    
                                    <!-- TANGGAL MASUK RAK -->
                                    <td class="px-5 py-4 font-mono font-semibold text-slate-600 dark:text-gray-400">
                                        {{ !empty($item['storage']['stored_at']) ? \Carbon\Carbon::parse($item['storage']['stored_at'])->format('d M Y H:i') : '-' }}
                                    </td>
                                    
                                    <!-- DURASI TERSIMPAN -->
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $isDonation = ($item['storage']['is_donation_candidate'] ?? false) || ($item['storage']['days_stored'] ?? 0) >= 90;
                                            $days = $item['storage']['days_stored'] ?? 0;
                                            $formattedDays = strtoupper($item['storage']['days_stored_formatted'] ?? '');
                                        @endphp
                                        @if($isDonation)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-rose-500 text-white border border-rose-500 shadow-sm shadow-rose-500/10">
                                                ⚠️ {{ $days }} HARI (&gt; 3 BLN)
                                            </span>
                                        @elseif($days === 0 || $formattedDays === 'HARI INI')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-slate-100 text-slate-600 border border-slate-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                                HARI INI
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-slate-100 text-slate-600 border border-slate-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                                {{ $days }} HARI
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- AKSI -->
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Whatsapp Notifikasi -->
                                            <a href="{{ $this->getWhatsAppLink($item, 'notification') }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[9px] tracking-wider rounded-xl shadow-md shadow-emerald-500/20 hover:scale-[1.02] transition-all uppercase">
                                                💬 NOTIFIKASI
                                            </a>
                                            
                                            <!-- Masuk Donasi Button (Only visible if donation candidate) -->
                                            @if($isDonation)
                                                <a href="{{ $this->getWhatsAppLink($item, 'donation') }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-[9px] tracking-wider rounded-xl shadow-md shadow-rose-500/20 hover:scale-[1.02] transition-all uppercase">
                                                    🎁 MASUK DONASI
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3 opacity-30">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-xs">Tidak ada data sepatu di rak ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination controls --}}
                @if($totalPages > 1)
                    <div class="flex items-center justify-between gap-4 mt-6">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                            HALAMAN {{ $currentPage }} DARI {{ $totalPages }}
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            {{-- Previous Page --}}
                            @if($currentPage > 1)
                                <button wire:click="setPage({{ $currentPage - 1 }})" 
                                    class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-xl border border-slate-200/50 dark:border-gray-800 text-[10px] font-black uppercase tracking-wider transition-all">
                                    Sebelumnya
                                </button>
                            @else
                                <span class="px-3 py-1.5 bg-slate-50 dark:bg-gray-800 text-slate-300 dark:text-gray-600 rounded-xl border border-slate-200/20 dark:border-gray-800/30 text-[10px] font-black uppercase tracking-wider cursor-not-allowed">
                                    Sebelumnya
                                </span>
                            @endif

                            {{-- Numeric Pages --}}
                            @php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                            @endphp
                            @for($i = $startPage; $i <= $endPage; $i++)
                                <button wire:click="setPage({{ $i }})" 
                                    class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all {{ $i === $currentPage ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 border border-slate-200/50 dark:border-gray-800' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            {{-- Next Page --}}
                            @if($currentPage < $totalPages)
                                <button wire:click="setPage({{ $currentPage + 1 }})" 
                                    class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-xl border border-slate-200/50 dark:border-gray-800 text-[10px] font-black uppercase tracking-wider transition-all">
                                    Berikutnya
                                </button>
                            @else
                                <span class="px-3 py-1.5 bg-slate-50 dark:bg-gray-800 text-slate-300 dark:text-gray-600 rounded-xl border border-slate-200/20 dark:border-gray-800/30 text-[10px] font-black uppercase tracking-wider cursor-not-allowed">
                                    Berikutnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
