<div class="space-y-6 py-6" wire:init="loadData">
    @if (!$isLoaded)
        {{-- Skeleton Loaders --}}
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 mb-8 animate-pulse">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-16 h-16 rounded-2xl bg-slate-800"></div>
                <div class="space-y-2">
                    <div class="h-6 w-48 bg-slate-800 rounded"></div>
                    <div class="h-3 w-80 bg-slate-800 rounded"></div>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
                <div class="w-[120px] h-20 bg-slate-800 rounded-2xl"></div>
            </div>
        </div>
        <div class="h-20 bg-slate-900 border border-slate-800 rounded-3xl animate-pulse mb-6"></div>
        <div class="h-96 bg-slate-900 border border-slate-800 rounded-3xl animate-pulse"></div>

    @elseif ($errorMessage && empty($summary))
        {{-- Alert error --}}
        <div class="p-6 bg-rose-950/20 border border-rose-900 rounded-3xl flex items-start gap-4">
            <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-lg text-rose-400">Gagal Memuat Data</h3>
                <p class="text-sm text-rose-500 mt-1">Sistem gagal mengambil data performa KPI CS dari API: {{ $errorMessage }}</p>
                <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                    Coba Lagi
                </button>
            </div>
        </div>
    @else
        {{-- Premium Dark Layout Container --}}
        <div class="text-slate-100 bg-[#070b13] border border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            {{-- Header Background Glow --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>

            {{-- Mock Data Alert Pill --}}
            @if($isMock)
                <div class="mb-4 flex items-center justify-between bg-amber-500/10 border border-amber-500/20 px-4 py-2.5 rounded-xl">
                    <div class="flex items-center gap-2 text-xs font-semibold text-amber-400">
                        <span>⚠️</span>
                        <span>Mode Fallback / Mock Data Aktif karena API utama memerlukan sesi browser (Cookie Auth) atau tidak terjangkau.</span>
                    </div>
                    <button wire:click="refreshData" class="text-xs font-black uppercase text-amber-400 hover:underline">
                        Coba Sinkron Ulang
                    </button>
                </div>
            @endif

            {{-- Header Row --}}
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 relative z-10">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-teal-950/40 text-teal-400 border border-teal-800/60 rounded-full text-[10px] font-black uppercase tracking-widest">
                            KPI LEADERBOARD
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                        <span class="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">CS Performance</span> 
                        <span class="text-slate-100">&</span> 
                        <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Revenue Leaderboard</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium max-w-3xl leading-relaxed">
                        Data performa operasional Customer Service (CS) real-time yang dihitung otomatis berdasarkan verifikasi invoice serta distribusi lead per-channel.
                    </p>
                </div>

                {{-- Date Selector and Action Buttons --}}
                <div class="flex flex-wrap items-center gap-4 bg-slate-900/60 border border-slate-800/80 p-3 rounded-2xl w-full lg:w-auto">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 pl-1">PERIODE TANGGAL</span>
                        <div class="w-full sm:w-[220px] bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 flex items-center justify-between shadow-inner cursor-pointer"
                            x-data="{
                                picker: null,
                                init() {
                                    this.picker = flatpickr($refs.picker, {
                                        mode: 'range',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: [@js($startDate), @js($endDate)],
                                        onClose: (selectedDates) => {
                                            if (selectedDates.length === 2) {
                                                const start = this.picker.formatDate(selectedDates[0], 'Y-m-d');
                                                const end = this.picker.formatDate(selectedDates[1], 'Y-m-d');
                                                $wire.updateDateRange(start, end);
                                            }
                                        }
                                    });
                                }
                            }">
                            <div class="flex items-center gap-2 w-full" x-ref="picker">
                                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <input type="text" readonly
                                    class="bg-transparent border-none text-[11px] font-bold text-slate-300 focus:ring-0 p-0 w-full cursor-pointer outline-none"
                                    value="{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end self-end h-10">
                        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-[11px] rounded-xl transition-all flex items-center gap-1.5 border border-slate-700">
                            <span>📊</span>
                            <span>Forecasting</span>
                        </button>
                    </div>

                    <div class="flex items-end self-end h-10">
                        <button wire:click="refreshData" class="p-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 rounded-xl transition-all active:scale-95 text-slate-400 hover:text-white" title="Refresh Data">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SIX KPI CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8 relative z-10">
                
                {{-- 1. TOP CS PERFORMER --}}
                <div class="bg-[#0e1422]/90 border-2 border-yellow-500/80 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-yellow-400 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-500/5 rounded-full blur-xl"></div>
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-yellow-500 uppercase tracking-widest">TOP CS PERFORMER</span>
                        <div class="w-9 h-9 rounded-xl bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20 text-lg relative">
                            👑
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-[8px] font-black text-white border border-[#070b13]">1</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-white tracking-tight block">
                            {{ $summary['top_cs']['name'] ?? '-' }}
                        </span>
                        <span class="text-[10px] font-black text-emerald-400 mt-1 block">
                            {{ $summary['top_cs']['closing'] ?? 0 }} Cls • Rp {{ number_format($summary['top_cs']['revenue'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- 2. TOTAL INVOICE REVENUE --}}
                <div class="bg-[#0e1422]/70 border border-slate-800/80 rounded-2xl p-5 shadow-md hover:border-slate-700/80 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TOTAL INVOICE REVENUE</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 text-emerald-400 text-base">
                            $
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-xl font-black text-white tracking-tight block">
                            Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-semibold text-slate-500 mt-1 block uppercase">
                            Verified from SPKs
                        </span>
                    </div>
                </div>

                {{-- 3. TOTAL CLOSING (CONVERTED) --}}
                <div class="bg-[#0e1422]/70 border border-slate-800/80 rounded-2xl p-5 shadow-md hover:border-slate-700/80 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TOTAL CLOSING (CONVERTED)</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 text-indigo-400 text-base">
                            ✓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-xl font-black text-white tracking-tight block">
                            {{ $summary['total_closing'] ?? 0 }} Closing
                        </span>
                        <span class="text-[10px] font-black text-emerald-400 mt-1 block">
                            {{ $summary['total_closing_detail']['direct'] ?? 0 }} Dir / {{ $summary['total_closing_detail']['followup'] ?? 0 }} FU
                        </span>
                    </div>
                </div>

                {{-- 4. TOTAL SEPATU DITERIMA --}}
                <div class="bg-[#0e1422]/70 border border-slate-800/80 rounded-2xl p-5 shadow-md hover:border-slate-700/80 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TOTAL SEPATU DITERIMA</span>
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center border border-teal-500/20 text-teal-400 text-base">
                            ✓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-xl font-black text-white tracking-tight block">
                            {{ $summary['total_sepatu_diterima'] ?? 0 }} Pasang
                        </span>
                        <span class="text-[10px] font-black text-cyan-400 mt-1 block">
                            {{ $summary['total_sepatu_diterima_detail']['online'] ?? 0 }} OL / {{ $summary['total_sepatu_diterima_detail']['offline'] ?? 0 }} OFF
                        </span>
                    </div>
                </div>

                {{-- 5. TOTAL SPK PENDING --}}
                <div class="bg-[#0e1422]/70 border border-slate-800/80 rounded-2xl p-5 shadow-md hover:border-slate-700/80 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest font-sans">TOTAL SPK PENDING</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-amber-500 text-base">
                            🕒
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-xl font-black text-white tracking-tight block">
                            {{ $summary['total_spk_pending'] ?? 0 }} Pasang
                        </span>
                        <span class="text-[10px] font-semibold text-slate-500 mt-1 block uppercase">
                            Belum di-receive workshop
                        </span>
                    </div>
                </div>

                {{-- 6. TOTAL BATAL (TRASH) --}}
                <div class="bg-[#0e1422]/70 border border-slate-800/80 rounded-2xl p-5 shadow-md hover:border-slate-700/80 transition-all flex flex-col justify-between min-h-[145px]">
                    <div class="flex items-start justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TOTAL BATAL (TRASH)</span>
                        <div class="w-9 h-9 rounded-xl bg-rose-500/10 flex items-center justify-center border border-rose-500/20 text-rose-500 text-base">
                            ✕
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-xl font-black text-white tracking-tight block">
                            {{ $summary['total_batal'] ?? 0 }} Pasang
                        </span>
                        <span class="text-[10px] font-semibold text-slate-500 mt-1 block uppercase leading-tight">
                            Data di tempat sampah (/reception/trash)
                        </span>
                    </div>
                </div>

            </div>

            {{-- MAIN LEADERBOARD TABLE CARD --}}
            <div class="bg-[#0a0f1d] border border-slate-800/60 rounded-3xl p-6 shadow-xl relative z-10">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-cyan-400 rounded-full"></span>
                        <h2 class="text-md font-extrabold uppercase tracking-wider text-white">
                            RANGKING EFISIENSI & HASIL CS
                        </h2>
                    </div>

                    {{-- Legend --}}
                    <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-wider">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400">ONLINE</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span class="text-slate-400">OFFLINE</span>
                        </div>
                    </div>
                </div>

                {{-- Table Container --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-4 w-16">RANK</th>
                                <th class="px-4 py-4">CS AGENT</th>
                                <th class="px-4 py-4 text-center">INTAKE (SEPATU MASUK)</th>
                                <th class="px-4 py-4 text-center">CLOSING (CONVERTED)</th>
                                <th class="px-4 py-4 text-center">SEPATU DITERIMA</th>
                                <th class="px-4 py-4 text-center">SPK PENDING</th>
                                <th class="px-4 py-4 text-center">BATAL (TRASH)</th>
                                <th class="px-4 py-4 text-right">REVENUE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-850 font-black text-slate-300 text-xs">
                            @forelse($perCs as $index => $cs)
                                <tr class="hover:bg-slate-900/20 transition-all">
                                    {{-- Rank Badge --}}
                                    <td class="px-4 py-4">
                                        @if($index === 0)
                                            <span class="text-xl">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-xl">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-xl">🥉</span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-900 border border-slate-800 text-[10px] font-extrabold text-slate-400">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- CS Agent info --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-extrabold text-xs {{ $cs['avatar_color'] ?? 'bg-indigo-600' }}">
                                                {{ substr($cs['cs_name'], 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-slate-100 font-extrabold text-sm">{{ $cs['cs_name'] }}</span>
                                                <span class="text-[9px] text-slate-500 font-black uppercase tracking-widest mt-0.5">
                                                    TOTAL {{ $cs['leads'] }} LEADS
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Intake --}}
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm text-slate-100 font-extrabold font-mono">{{ $cs['intake'] }} Psg</span>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-[9px] font-black uppercase tracking-tight">
                                                <span class="text-emerald-400">{{ $cs['intake_detail']['online'] ?? 0 }} OL</span>
                                                <span class="text-slate-600">•</span>
                                                <span class="text-indigo-400">{{ $cs['intake_detail']['offline'] ?? 0 }} OFF</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Closing --}}
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm text-slate-100 font-extrabold font-mono">{{ $cs['closing'] }} Closing</span>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-[9px] font-black uppercase tracking-tight">
                                                <span class="text-emerald-400">Dir: {{ $cs['closing_detail']['direct'] ?? 0 }}</span>
                                                <span class="text-slate-600">/</span>
                                                <span class="text-amber-500">FU: {{ $cs['closing_detail']['followup'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Sepatu Diterima --}}
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm text-slate-100 font-extrabold font-mono">{{ $cs['sepatu_diterima'] }} Psg</span>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-[9px] font-black uppercase tracking-tight">
                                                <span class="text-emerald-400">{{ $cs['sepatu_diterima_detail']['online'] ?? 0 }} OL</span>
                                                <span class="text-slate-600">•</span>
                                                <span class="text-indigo-400">{{ $cs['sepatu_diterima_detail']['offline'] ?? 0 }} OFF</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- SPK Pending --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-amber-500 font-extrabold font-mono">{{ $cs['spk_pending'] }} Psg</span>
                                    </td>

                                    {{-- Batal --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-rose-500 font-extrabold font-mono">{{ $cs['batal'] }} Psg</span>
                                    </td>

                                    {{-- Revenue --}}
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="text-sm text-emerald-400 font-black font-mono">
                                                Rp {{ number_format($cs['revenue'], 0, ',', '.') }}
                                            </span>
                                            <span class="text-[9px] text-slate-500 font-black uppercase tracking-widest mt-0.5">
                                                AIO: {{ $cs['closing'] > 0 ? 1 : 0 }} Psg/Order
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                        Tidak ada data performa CS ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- INTEGRASI REAL-TIME JSON API BOX --}}
            <div class="mt-8 bg-[#0a0f1d] border border-slate-800/60 rounded-3xl p-6 md:p-8 shadow-xl relative z-10">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="text-emerald-400">🟢</span>
                        <span class="text-yellow-500">⚡</span>
                        <h2 class="text-md font-extrabold uppercase tracking-wider text-white">
                            INTEGRASI REAL-TIME JSON API
                        </h2>
                    </div>
                    <span class="px-3 py-1 bg-emerald-950/40 text-emerald-400 border border-emerald-800/60 rounded-lg text-[9px] font-black uppercase tracking-wider">
                        ACTIVE ENDPOINT
                    </span>
                </div>
                
                <p class="text-xs text-slate-400 font-medium mb-6">
                    Gunakan endpoint di bawah ini untuk menarik data performa CS Leaderboard secara real-time dari aplikasi luar dengan format JSON yang terstruktur.
                </p>

                <div class="space-y-4">
                    <div>
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-2">REQUEST ENDPOINT URL (GET)</span>
                        <div class="flex flex-col sm:flex-row gap-3 w-full">
                            <div class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 font-mono text-xs text-[#38bdf8] break-all shadow-inner flex items-center">
                                {{ config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1') }}/cs-kpi-leaderboard?start_date={{ $startDate }}&end_date={{ $endDate }}&api_key={{ config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0') }}
                            </div>
                            <button 
                                onclick="navigator.clipboard.writeText('{{ config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1') }}/cs-kpi-leaderboard?start_date={{ $startDate }}&end_date={{ $endDate }}&api_key={{ config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0') }}'); Swal.fire({title: 'Berhasil', text: 'URL berhasil disalin ke clipboard!', icon: 'success', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false, background: '#0f172a', color: '#f1f5f9'});"
                                class="px-5 py-3 bg-teal-500 hover:bg-teal-400 text-slate-900 font-black text-xs rounded-xl shadow-lg shadow-teal-500/10 transition-all flex items-center justify-center gap-1.5 whitespace-nowrap self-stretch">
                                <span>📋</span>
                                <span>COPY URL</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-slate-800/60">
                        <div class="space-y-1">
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block">start_date</span>
                            <span class="text-[11px] font-semibold text-slate-400 block leading-normal">
                                Filter tanggal mulai (Format: 'YYYY-MM-DD', default: awal bulan ini)
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block">end_date</span>
                            <span class="text-[11px] font-semibold text-slate-400 block leading-normal">
                                Filter tanggal akhir (Format: 'YYYY-MM-DD', default: hari ini)
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block">Format Respons</span>
                            <span class="text-[11px] font-semibold text-slate-400 block leading-normal">
                                Payload berupa JSON dengan objek data per CS terurut descending berdasarkan closing.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- CSS styles for dark dashboard integrations --}}
    <style>
        .divide-slate-850 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgba(30, 41, 59, 0.5);
        }
        .bg-slate-850 {
            background-color: rgba(30, 41, 59, 0.4);
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</div>
