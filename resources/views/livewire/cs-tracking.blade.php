<div class="px-6 py-6 pb-20">
    {{-- Header & Filter Bar --}}
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-slate-200/40 dark:shadow-none">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-3xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/40 group">
                <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">Konsol Produktivitas</h1>
                    <span class="px-2 py-1 rounded-lg bg-amber-500 text-white text-[10px] font-black tracking-[0.2em]">TRACKING</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                    <span class="w-2h-2 rounded-full bg-slate-300"></span>
                    Mode Atribusi: Tanggal Kejadian (`_at`) • Akurasi Historis Aktif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-inner">
                <div class="flex items-center px-4 gap-3">
                    <input type="date" 
                        wire:model="startDate"
                        class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                    <span class="text-slate-300 dark:text-slate-700 font-black">—</span>
                    <input type="date" 
                        wire:model="endDate"
                        class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                </div>
                
                <button type="button"
                    wire:click="applyFilter" 
                    wire:loading.attr="disabled"
                    class="px-8 py-3 rounded-2xl bg-slate-800 dark:bg-amber-500 text-white text-[11px] font-black uppercase tracking-widest transition-all hover:scale-[1.02] shadow-lg shadow-slate-500/20">
                    <span wire:loading.remove wire:target="applyFilter">TERAPKAN</span>
                    <span wire:loading wire:target="applyFilter" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Impact Summaries --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Total Closing --}}
        <div class="bg-indigo-600 rounded-[40px] p-8 shadow-2xl shadow-indigo-500/30 text-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-indigo-100/60 mb-3 block">Total Closing</p>
            <h3 class="text-5xl font-black tracking-tighter leading-none">{{ number_format(collect($stats)->sum('total_closing_all')) }}</h3>
            <div class="mt-6 flex items-center gap-2 text-[10px] font-black text-indigo-100/40 uppercase tracking-widest">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                Penjualan Terkonfirmasi
            </div>
        </div>

        {{-- Direct Closing --}}
        <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors"></div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Closing Langsung</p>
            <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">{{ number_format(collect($stats)->sum('total_closing_direct')) }}</h3>
            <p class="text-[10px] font-black text-emerald-500/80 uppercase tracking-widest mt-4">Tanpa Follow-up</p>
        </div>

        {{-- FU Closing --}}
        <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl group-hover:bg-amber-500/10 transition-colors"></div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Closing Follow-up</p>
            <h3 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter leading-none">{{ number_format(collect($stats)->sum('total_closing_fu')) }}</h3>
            <p class="text-[10px] font-black text-amber-500/80 uppercase tracking-widest mt-4">Hasil Edukasi & FU</p>
        </div>

        {{-- Conversion Rate --}}
        @php 
            $totCl = collect($stats)->sum('total_closing_all');
            $totGr = collect($stats)->sum('total_greeting');
            $rate = $totGr > 0 ? round(($totCl / $totGr) * 100, 1) : 0;
        @endphp
        <div class="bg-slate-800 rounded-[40px] p-8 shadow-2xl shadow-slate-900/30 text-white overflow-hidden group">
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-3">Tingkat Konversi</p>
            <h3 class="text-5xl font-black tracking-tighter leading-none">{{ $rate }}%</h3>
            <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest mt-4">Efektivitas Sales / Greeting</p>
        </div>
    </div>

    {{-- Detailed Tracking Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden relative" wire:loading.class="opacity-50">
        <div wire:loading class="absolute inset-0 z-10 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-[2px]">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-[10px] font-black text-slate-800 dark:text-white uppercase tracking-[0.3em]">Menganalisis Riwayat...</span>
            </div>
        </div>

        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] border-b border-gray-50 dark:border-gray-800">
                    <th class="px-10 py-7">Identitas CS</th>
                    <th class="px-6 py-7 text-center">Greeting</th>
                    <th class="px-6 py-7 text-center">Konsultasi</th>
                    <th class="px-6 py-7 text-center text-amber-500">Aksi Follow Up</th>
                    <th class="px-6 py-7 text-center">Closing Langsung</th>
                    <th class="px-6 py-7 text-center">Closing Follow Up</th>
                    <th class="px-10 py-7 text-right">Total Closing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                @forelse($stats as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-10 py-7">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-[20px] bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500">
                                    {{ substr($row['contact_owner_name'] ?: '?', 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $row['contact_owner_name'] ?: 'Belum Ditentukan' }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">CS Aktif</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <span class="text-sm font-black text-slate-600 dark:text-slate-400">{{ number_format($row['total_greeting']) }}</span>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <span class="text-sm font-black text-slate-600 dark:text-slate-400">{{ number_format($row['total_konsul']) }}</span>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <span class="px-4 py-1.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-bold border border-amber-500/20">
                                {{ number_format($row['total_followed_up']) }} FU
                            </span>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <span class="text-sm font-black text-emerald-500">{{ number_format($row['total_closing_direct']) }}</span>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <span class="text-sm font-black text-amber-500">{{ number_format($row['total_closing_fu']) }}</span>
                        </td>
                        <td class="px-10 py-7 text-right">
                            <div class="flex flex-col items-end">
                                <span class="bg-indigo-600 text-white px-5 py-2 rounded-2xl text-xs font-black shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                                    {{ number_format($row['total_closing_all']) }}
                                </span>
                                @php
                                    $perf = $row['total_greeting'] > 0 ? round(($row['total_closing_all'] / $row['total_greeting']) * 100, 1) : 0;
                                @endphp
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-2">{{ $perf }}% Eff.</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-32 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">No data records found for this range</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        input[type="date"]::-webkit-calendar-picker-indicator { 
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }
        input[type="date"] { position: relative; }
    </style>
</div>
