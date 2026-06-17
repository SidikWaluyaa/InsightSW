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
                    <div class="w-[150px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
                    <div class="w-[150px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
                    <div class="w-[150px] h-20 bg-slate-100 dark:bg-gray-800 rounded-2xl"></div>
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
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Sortir: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            {{-- FIRST ROW: Header & KPI Cards in one unified design --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Icon Box -->
                    <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-3xl">
                        🔄
                    </div>
                    <div>
                        <h2 class="font-black text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                            Data Sortir <span class="text-indigo-500 font-bold">(Sedang Disortir)</span>
                        </h2>
                        <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1">
                            DAFTAR SEMUA ORDER YANG SEDANG BERADA DALAM PROSES SORTIR DI WORKSHOP
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 items-center w-full lg:w-auto">
                    <!-- Total items in sortir -->
                    <div class="w-full sm:w-[160px] bg-indigo-50/50 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">TOTAL DI SORTIR</span>
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400 block my-1 font-mono">
                            {{ number_format($summary['total_items_in_sortir'] ?? 0) }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Sepatu Sedang Disortir</span>
                    </div>

                    <!-- Overdue items -->
                    <div class="w-full sm:w-[160px] bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider block">OVERDUE (&gt; 3 HARI)</span>
                        <span class="text-3xl font-black text-rose-600 dark:text-rose-400 block my-1 font-mono">
                            {{ number_format($summary['overdue_items_count'] ?? 0) }}
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Tertahan Lebih dari 3 Hari</span>
                    </div>
                    
                    <!-- Average duration -->
                    <div class="w-full sm:w-[160px] bg-amber-50/50 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">RERATA DURASI</span>
                        <span class="text-3xl font-black text-amber-600 dark:text-amber-400 block my-1 font-mono">
                            {{ $summary['average_days_in_sortir'] ?? '0' }} <span class="text-xs font-bold">HARI</span>
                        </span>
                        <span class="text-[8px] font-semibold text-slate-400 uppercase block">Rata-rata Waktu Sortir</span>
                    </div>
                </div>
            </div>

            {{-- SECOND ROW: Tabs & Dropdowns Filter --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
                    <!-- Status Tabs -->
                    <div class="flex p-1 bg-slate-100 dark:bg-gray-850 rounded-2xl self-start">
                        <button wire:click="setStatusFilter('')" 
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all uppercase tracking-wider {{ $selectedStatus === '' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                            Semua Status
                        </button>
                        <button wire:click="setStatusFilter('on_track')" 
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all uppercase tracking-wider {{ $selectedStatus === 'on_track' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                            On Track
                        </button>
                        <button wire:click="setStatusFilter('overdue')" 
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all uppercase tracking-wider {{ $selectedStatus === 'overdue' ? 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                            Stagnan &gt; 3 Hari
                        </button>
                    </div>

                    <!-- Right Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Search Input -->
                        <div class="relative w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" 
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all shadow-sm"
                                placeholder="Cari SPK, Pelanggan, Brand...">
                        </div>

                        <!-- Dropdown Semua Jasa -->
                        <div class="w-full sm:w-44">
                            <select wire:model.live="selectedService" 
                                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none cursor-pointer transition-all shadow-sm">
                                <option value="">Semua Jasa</option>
                                @foreach($allServices as $service)
                                    <option value="{{ $service }}">{{ strtoupper($service) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Semua Kategori -->
                        <div class="w-full sm:w-44">
                            <select wire:model.live="selectedCategory" 
                                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none cursor-pointer transition-all shadow-sm">
                                <option value="">Semua Kategori</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat }}">{{ strtoupper($cat) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Estimasi Selesai Status -->
                        <div class="w-full sm:w-36">
                            <select wire:model.live="selectedEstimation" 
                                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none cursor-pointer transition-all shadow-sm">
                                <option value="">Semua Estimasi</option>
                                <option value="set">Sudah Set</option>
                                <option value="unset">Belum Set</option>
                            </select>
                        </div>

                        <!-- Date Range Picker for Estimasi Selesai -->
                        <div class="w-full sm:w-[220px] bg-slate-50 dark:bg-gray-800/40 border border-slate-200/50 dark:border-gray-800 rounded-2xl px-3 py-2.5 flex items-center justify-between shadow-sm cursor-pointer"
                            x-data="{
                                picker: null,
                                init() {
                                    this.picker = flatpickr($refs.picker, {
                                        mode: 'range',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: [@js($estimationStartDate), @js($estimationEndDate)],
                                        onClose: (selectedDates) => {
                                            if (selectedDates.length === 2) {
                                                const start = this.picker.formatDate(selectedDates[0], 'Y-m-d');
                                                const end = this.picker.formatDate(selectedDates[1], 'Y-m-d');
                                                @this.set('estimationStartDate', start);
                                                @this.set('estimationEndDate', end);
                                            } else if (selectedDates.length === 0) {
                                                @this.set('estimationStartDate', '');
                                                @this.set('estimationEndDate', '');
                                            }
                                        }
                                    });
                                }
                            }">
                            <div class="flex items-center gap-2 w-full" x-ref="picker">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <input type="text" readonly
                                    class="bg-transparent border-none text-[11px] font-black text-slate-700 dark:text-gray-300 focus:ring-0 p-0 w-full cursor-pointer outline-none placeholder-slate-400"
                                    placeholder="RENTANG ESTIMASI"
                                    value="{{ $estimationStartDate && $estimationEndDate ? \Carbon\Carbon::parse($estimationStartDate)->format('d/m/y') . ' - ' . \Carbon\Carbon::parse($estimationEndDate)->format('d/m/y') : 'RENTANG ESTIMASI' }}">
                            </div>
                            @if($estimationStartDate || $estimationEndDate)
                                <button type="button" @click.stop="picker.clear(); @this.set('estimationStartDate', ''); @this.set('estimationEndDate', '');" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- PDF Download Link -->
                        <a href="{{ route('workshop-data-sortir.export-pdf', ['searchTerm' => $searchTerm, 'selectedStatus' => $selectedStatus, 'selectedService' => $selectedService, 'selectedCategory' => $selectedCategory, 'selectedEstimation' => $selectedEstimation, 'estimationStartDate' => $estimationStartDate, 'estimationEndDate' => $estimationEndDate]) }}" 
                            target="_blank"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-2xl text-center shadow-md shadow-indigo-500/10 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span>📄</span>
                            <span>CETAK LAPORAN PDF</span>
                        </a>

                        @if($searchTerm || $selectedStatus || $selectedService || $selectedCategory || $selectedEstimation || $estimationStartDate || $estimationEndDate)
                            <!-- Reset -->
                            <button wire:click="resetFilters" 
                                class="p-2.5 bg-slate-150 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-bold rounded-2xl transition-all shadow-sm"
                                title="Reset Filter">
                                🔄
                            </button>
                        @endif

                        <!-- Refresh -->
                        <button wire:click="refreshData" 
                            class="p-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-800/40 dark:hover:bg-gray-850 text-slate-600 dark:text-gray-300 rounded-2xl border border-slate-200/50 dark:border-gray-800 transition-all active:scale-95 shadow-sm"
                            title="Refresh Data">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- THIRD ROW: Sortir Table List --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">
                            DAFTAR PROSES SORTIR
                        </h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">DAFTAR SEPATU DI BAGIAN SORTIR DENGAN ESTIMASI DAN STATUS KELAYAKAN</p>
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
                                <th class="px-5 py-4">LAYANAN / JASA</th>
                                <th class="px-5 py-4">WAKTU MASUK SORTIR</th>
                                <th class="px-5 py-4 text-center">ESTIMASI SELESAI</th>
                                <th class="px-5 py-4 text-center">DURASI SORTIR</th>
                                <th class="px-5 py-4 text-center">STATUS / WARNING</th>
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
                                            <span class="text-slate-800 dark:text-white font-black">{{ $item['customer_name'] ?? '-' }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- DETAIL SEPATU -->
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-white font-black">
                                                {{ $item['shoe_brand'] ?: 'UNKNOWN' }}
                                            </span>
                                            @if(!empty($item['shoe_type']))
                                                <span class="text-[9px] text-slate-400 font-normal font-mono mt-0.5">
                                                    {{ $item['shoe_type'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- LAYANAN / JASA -->
                                    <td class="px-5 py-4">
                                        @if(!empty($item['services']))
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @foreach($item['services'] as $service)
                                                    <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-gray-850 text-slate-600 dark:text-slate-400 rounded text-[8px] font-black uppercase border border-slate-200/40 dark:border-gray-800/60 tracking-tight">
                                                        {{ $service }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 font-normal">-</span>
                                        @endif
                                    </td>
                                    
                                    <!-- WAKTU MASUK SORTIR -->
                                    <td class="px-5 py-4 font-mono font-semibold text-slate-600 dark:text-gray-400">
                                        {{ $item['entered_sortir_at_formatted'] ?: '-' }}
                                    </td>
                                    
                                    <!-- ESTIMASI SELESAI -->
                                    <td class="px-5 py-4 text-center">
                                        @if($item['has_estimation'])
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">
                                                {{ strtoupper($item['estimation_date_formatted']) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-amber-50 text-amber-700 border border-amber-200/50 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30">
                                                BELUM SET
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- DURASI SORTIR -->
                                    <td class="px-5 py-4 text-center font-mono font-black text-slate-800 dark:text-white">
                                        {{ $item['days_in_sortir'] ?? 0 }} HARI
                                    </td>

                                    <!-- STATUS / WARNING -->
                                    <td class="px-5 py-4 text-center">
                                        @if($item['is_overdue'] ?? false)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-rose-500 text-white border border-rose-500 shadow-sm shadow-rose-500/10">
                                                {{ strtoupper($item['warning_message'] ?: 'STAGNAN') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-emerald-500 text-white border border-emerald-500 shadow-sm shadow-emerald-500/10">
                                                ON TRACK
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3 opacity-30">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-xs">Tidak ada data sortir ditemukan.</p>
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
                                    class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all {{ $i === $currentPage ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-50 hover:bg-slate-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 border border-slate-200/50 dark:border-gray-800' }}">
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</div>
