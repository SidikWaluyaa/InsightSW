<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Dashboard Gudang <span class="text-xs px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 ml-2 uppercase font-black tracking-widest animate-pulse">Live API</span>
            </h2>
            
            <div class="flex items-center gap-3">
                @if($metadata && isset($metadata['last_updated']))
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">Update Terakhir</span>
                        <span class="text-xs font-semibold text-slate-600 dark:text-gray-300 font-mono">
                            {{ \Carbon\Carbon::parse($metadata['last_updated'])->diffForHumans() }}
                        </span>
                    </div>
                @endif
                
                {{-- Date Range Picker --}}
                <div class="flex items-center gap-3 bg-slate-100 dark:bg-gray-800/50 p-2 rounded-2xl border border-gray-200/50 dark:border-gray-800"
                    x-data="{
                        picker: null,
                        init() {
                            this.picker = flatpickr($refs.picker, {
                                mode: 'range',
                                dateFormat: 'Y-m-d',
                                defaultDate: ['{{ $startDate }}', '{{ $endDate }}'],
                                onClose: (selectedDates) => {
                                    if (selectedDates.length === 2) {
                                        const start = this.picker.formatDate(selectedDates[0], 'Y-m-d');
                                        const end = this.picker.formatDate(selectedDates[1], 'Y-m-d');
                                        @this.set('startDate', start);
                                        @this.set('endDate', end);
                                    }
                                }
                            });
                        }
                    }">
                    <div class="flex items-center gap-2 cursor-pointer" x-ref="picker">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <input type="text" readonly
                            class="bg-transparent border-none text-[11px] font-black text-emerald-600 dark:text-emerald-400 focus:ring-0 p-0 w-[180px] cursor-pointer text-center outline-none"
                            placeholder="RENTANG TANGGAL"
                            value="{{ $startDate && $endDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : 'RENTANG TANGGAL' }}">
                    </div>
                </div>

                <button wire:click="refreshData" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800/50 dark:hover:bg-gray-800 text-slate-600 dark:text-gray-300 rounded-xl border border-gray-200/50 dark:border-gray-800 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 py-6" wire:init="loadData">
        @if (!$isLoaded)
            {{-- Skeleton Loaders --}}
            <div class="h-32 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @for ($i = 0; $i < 9; $i++)
                    <div class="h-28 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
                @endfor
            </div>

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
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Gudang: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            {{-- FIRST ROW: Primary Warehouse Metrics Card --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-gray-100 dark:divide-gray-800 text-center">
                    {{-- SPK PENDING --}}
                    <div class="p-4 flex flex-col justify-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                            <span class="inline-block mr-1">📥</span> SPK Pending
                        </p>
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white font-sans tracking-tight">
                            {{ number_format($summary['pending_reception'] ?? 0) }}
                        </h3>
                    </div>

                    {{-- DI FINISH (NOT RACKED) --}}
                    <div class="p-4 flex flex-col justify-center">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">
                            <span class="inline-block mr-1">✨</span> Di Finish (Not Racked)
                        </p>
                        <h3 class="text-3xl font-black text-amber-500 font-sans tracking-tight">
                            {{ number_format($summary['finished_not_stored'] ?? 0) }}
                        </h3>
                    </div>

                    {{-- DI RAK (STORED) --}}
                    <div class="p-4 flex flex-col justify-center">
                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">
                            <span class="inline-block mr-1">📦</span> Di Rak (Stored)
                        </p>
                        <h3 class="text-3xl font-black text-blue-500 font-sans tracking-tight">
                            {{ number_format($summary['stored_items'] ?? 0) }}
                        </h3>
                    </div>

                    {{-- SIAP DIAMBIL (READY) --}}
                    <div class="p-4 flex flex-col justify-center">
                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">
                            <span class="inline-block mr-1">🚀</span> Siap Diambil (Ready)
                        </p>
                        <h3 class="text-3xl font-black text-emerald-500 font-sans tracking-tight">
                            {{ number_format($summary['ready_for_pickup'] ?? 0) }}
                        </h3>
                    </div>
                </div>
            </div>

            {{-- SECOND ROW: 9 Grid Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1: SEPATU MASUK (BEFORE) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden group hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">1. Sepatu Masuk (Before)</p>
                            <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                                {{ number_format($summary['incoming_day'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Diterima Fisik di Gudang</p>
                        </div>
                        <span class="p-2.5 bg-slate-50 dark:bg-gray-800 rounded-2xl text-slate-500">📥</span>
                    </div>
                </div>

                <!-- Card 2: SPK PRINT (OTW WS) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">2. SPK Print (Otw WS)</p>
                            <h3 class="text-3xl font-black text-blue-500 tracking-tight">
                                {{ number_format($summary['spk_print'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Dikirim ke Reparasi / Manifest</p>
                        </div>
                        <span class="p-2.5 bg-blue-500/5 rounded-2xl text-blue-500">🚚</span>
                    </div>
                </div>

                <!-- Card 3: SPK TERTAHAN (QC REJECT) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">3. SPK Tertahan (QC Reject)</p>
                            <h3 class="text-3xl font-black {{ ($summary['qc_reject'] ?? 0) > 0 ? 'text-rose-500' : 'text-slate-800 dark:text-white' }} tracking-tight">
                                {{ number_format($summary['qc_reject'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Gagal Penerimaan Awal</p>
                        </div>
                        <span class="p-2.5 bg-rose-500/5 rounded-2xl text-rose-500">⚠️</span>
                    </div>
                </div>

                <!-- Card 4: AFTER MASUK -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">4. After Masuk</p>
                            <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                                {{ number_format($summary['after_masuk'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Selesai Reparasi Masuk Rak</p>
                        </div>
                        <span class="p-2.5 bg-slate-50 dark:bg-gray-800 rounded-2xl text-slate-500">✨</span>
                    </div>
                </div>

                <!-- Card 5: SEPATU KELUAR -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">5. Sepatu Keluar</p>
                            <h3 class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">
                                {{ number_format($summary['sepatu_keluar'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Pengambilan & Kirim Lunas</p>
                        </div>
                        <span class="p-2.5 bg-blue-500/5 rounded-2xl text-blue-500">📤</span>
                    </div>
                </div>

                <!-- Card 6: RAK INBOUND (TRANSIT) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">6. Rak Inbound (Transit)</p>
                            <h3 class="text-3xl font-black text-amber-600 dark:text-amber-500 tracking-tight">
                                {{ number_format($summary['inbound_inventory'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Fisik di Rak Penerimaan/Sebelum</p>
                        </div>
                        <span class="p-2.5 bg-amber-500/5 rounded-2xl text-amber-500">📥</span>
                    </div>
                </div>

                <!-- Card 7: RAK FINISH (SELESAI) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">7. Rak Finish (Selesai)</p>
                            <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                                {{ number_format($summary['finish_inventory'] ?? 0) }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Pasang</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">Fisik di Rak Selesai/Siap Ambil</p>
                        </div>
                        <span class="p-2.5 bg-slate-50 dark:bg-gray-800 rounded-2xl text-slate-400">📦</span>
                    </div>
                </div>

                <!-- Card 8: CLEARANCE RATE INBOUND -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">8. Clearance Rate Inbound</p>
                            <h3 class="text-3xl font-black {{ ($summary['clearance_rate_before'] ?? 0) < 0 ? 'text-amber-500' : 'text-emerald-500' }} tracking-tight">
                                {{ ($summary['clearance_rate_before'] ?? 0) > 0 ? '+' : '' }}{{ $summary['clearance_rate_before'] ?? 0 }}%
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">
                                @if(($summary['clearance_rate_before'] ?? 0) < 0)
                                    <span class="text-[9px] font-black tracking-widest px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 uppercase">Antrean Clog</span>
                                @else
                                    <span class="text-[9px] font-black tracking-widest px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 uppercase">Lancar</span>
                                @endif
                                <span class="ml-1 text-slate-400 font-medium">Inbound Flow Balance</span>
                            </p>
                        </div>
                        <span class="p-2.5 bg-amber-500/5 rounded-2xl text-amber-500">⚖️</span>
                    </div>
                </div>

                <!-- Card 9: CLEARANCE RATE OUTBOUND -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-3">9. Clearance Rate Outbound</p>
                            <h3 class="text-3xl font-black {{ ($summary['clearance_rate_after'] ?? 0) < 0 ? 'text-amber-500' : 'text-emerald-500' }} tracking-tight">
                                {{ ($summary['clearance_rate_after'] ?? 0) > 0 ? '+' : '' }}{{ $summary['clearance_rate_after'] ?? 0 }}%
                            </h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-2">
                                <span class="text-[9px] font-black tracking-widest px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 uppercase">Ops Optimal</span>
                                <span class="ml-1 text-slate-400 font-medium">Outbound Flow Balance</span>
                            </p>
                        </div>
                        <span class="p-2.5 bg-emerald-500/5 rounded-2xl text-emerald-500">🔄</span>
                    </div>
                </div>
            </div>


        @endif
    </div>



    {{-- Chart.js & Flatpickr Assets --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</div>
