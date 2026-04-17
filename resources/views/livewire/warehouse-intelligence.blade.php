<div class="p-6 space-y-8 bg-slate-950 min-h-screen text-slate-200">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 rounded-2xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-3xl font-black tracking-tight uppercase text-white leading-none">Intelligence Hub</h2>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.4em] mt-2">Audit Operasional & Prediksi Intelijen</p>
                </div>
            </div>
        </div>

        <button wire:click="syncIntelligence" wire:loading.attr="disabled"
            class="group px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all duration-300 shadow-xl shadow-indigo-900/40 flex items-center gap-3 active:scale-95">
            <svg wire:loading.remove wire:target="syncIntelligence" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg wire:loading wire:target="syncIntelligence" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="tracking-widest uppercase text-xs">Jalankan Audit Data</span>
        </button>
    </div>

    {{-- Ringkasan Penilaian Risiko --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-900/50 border border-slate-800 p-8 rounded-[2rem] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-20 h-20 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" /></svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Total Valuasi Aset</p>
            <h3 class="text-4xl font-black text-white italic">Rp {{ number_format($summaryStats['total_valuation'] ?? 0, 0, ',', '.') }}</h3>
            <p class="mt-4 text-[10px] text-emerald-400 font-bold uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Valuasi Fisik Aktif
            </p>
        </div>

        <div class="bg-slate-900/50 border border-slate-800 p-8 rounded-[2rem] relative overflow-hidden group {{ $summaryStats['sla_violations'] > 0 ? 'ring-2 ring-amber-500/20' : '' }}">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-20 h-20 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" /></svg>
            </div>
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Pelanggaran Bottleneck</p>
            <h3 class="text-4xl font-black {{ $summaryStats['sla_violations'] > 0 ? 'text-amber-500' : 'text-white' }} italic">{{ $summaryStats['sla_violations'] }} <span class="text-lg">SPK</span></h3>
            <p class="mt-4 text-[10px] {{ $summaryStats['sla_violations'] > 0 ? 'text-amber-400' : 'text-slate-400' }} font-bold uppercase tracking-widest">
                {{ $summaryStats['sla_violations'] > 0 ? 'Threshold SLA Terlampaui' : 'Performa Alur Normal' }}
            </p>
        </div>
    </div>

    {{-- Grid Analitik Utama --}}
    <div class="grid grid-cols-1 gap-8">
        {{-- Bagian 1: Kesiapan Produksi --}}
        <div class="bg-slate-900/50 border border-slate-800 rounded-[2.5rem] p-10">
            <h3 class="text-xl font-black text-white uppercase tracking-tight mb-8">Analisis Kesiapan Produksi</h3>
            
            <div class="space-y-10">
                {{-- Siap Produksi --}}
                <div class="group">
                    <div class="flex justify-between items-end mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                            <div>
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-1">Siap Produksi</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-white italic">{{ $readinessData['siap_count'] }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">SPK</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-emerald-400 italic">{{ number_format($readinessData['siap_pct']) }}%</span>
                        </div>
                    </div>
                    <div class="h-3 bg-slate-800 rounded-full overflow-hidden border border-slate-700/50">
                        <div class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(16,185,129,0.3)]" style="width: {{ $readinessData['siap_pct'] }}%"></div>
                    </div>
                </div>

                {{-- Masih Pengadaan --}}
                <div class="group">
                    <div class="flex justify-between items-end mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-8 bg-amber-500 rounded-full"></span>
                            <div>
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-1">Masih Pengadaan</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-white italic">{{ $readinessData['procurement_count'] }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">SPK</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-amber-400 italic">{{ number_format($readinessData['procurement_pct']) }}%</span>
                        </div>
                    </div>
                    <div class="h-3 bg-slate-800 rounded-full overflow-hidden border border-slate-700/50">
                        <div class="h-full bg-gradient-to-r from-amber-600 to-amber-400 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(245,158,11,0.3)]" style="width: {{ $readinessData['procurement_pct'] }}%"></div>
                    </div>
                </div>

                {{-- Belum Request --}}
                <div class="group">
                    <div class="flex justify-between items-end mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-8 bg-rose-500 rounded-full"></span>
                            <div>
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-1">Belum Request</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-white italic">{{ $readinessData['request_count'] }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">SPK</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-rose-400 italic">{{ number_format($readinessData['request_pct']) }}%</span>
                        </div>
                    </div>
                    <div class="h-3 bg-slate-800 rounded-full overflow-hidden border border-slate-700/50">
                        <div class="h-full bg-gradient-to-r from-rose-600 to-rose-400 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(244,63,94,0.3)]" style="width: {{ $readinessData['request_pct'] }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-12 p-6 bg-slate-950/50 rounded-2xl border border-slate-800/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Total SPK di Antrean Sortir</p>
                        <p class="text-xl font-bold text-white">{{ $readinessData['total_count'] }} <span class="text-xs text-slate-500 uppercase">Data Ditemukan</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Audit Bottleneck --}}
    <div class="bg-slate-900/50 border border-slate-800 rounded-[2.5rem] p-10 overflow-hidden">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight">Audit Bottleneck: Monitoring SLA</h3>
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mt-2">Threshold: > 3 Hari di Antrean Sortir</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-800">
                        <th class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Nomor SPK</th>
                        <th class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Tanggal Masuk</th>
                        <th class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                        <th class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Hari Tertahan</th>
                        <th class="py-4 px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Alert Pelanggaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($bottlenecks as $bt)
                        <tr class="group hover:bg-white/5 transition-colors">
                            <td class="py-6 px-4">
                                <span class="text-sm font-bold text-white group-hover:text-indigo-400 transition-colors">{{ $bt->spk_number }}</span>
                            </td>
                            <td class="py-6 px-4 text-center">
                                <span class="text-xs font-medium text-slate-400 font-mono">{{ $bt->entry_date ? \Carbon\Carbon::parse($bt->entry_date)->format('d/m/Y H:i') : '-' }}</span>
                            </td>
                            <td class="py-6 px-4 text-xs font-black italic text-slate-500">
                                {{ $bt->sortir_category }}
                            </td>
                            <td class="py-6 px-4 text-center">
                                <span class="px-4 py-1.5 rounded-lg bg-slate-950 text-amber-500 font-mono font-bold text-sm border border-slate-800">{{ $bt->days_in_sortir }} Hari</span>
                            </td>
                            <td class="py-6 px-4 text-right">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-500/10 border border-rose-500/20 rounded-xl">
                                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                    <span class="text-[9px] font-black text-rose-400 uppercase tracking-widest">BOTTLE-NECKED</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 uppercase text-[10px] font-bold tracking-widest">Tidak Ada Pelanggaran SLA Terdeteksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $bottlenecks->links() }}
        </div>
    </div>
</div>
