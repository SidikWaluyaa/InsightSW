<div class="p-4 sm:p-6 md:p-8 space-y-6 bg-slate-50 dark:bg-slate-950 min-h-screen transition-all duration-500 ease-in-out">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Catatan Pembaruan Keuangan</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium italic">Riwayat kapan terakhir kali data diambil dari server pusat</p>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu Update</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Oleh Siapa</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rentang Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Data Masuk</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kondisi</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white font-bold uppercase">
                                        {{ substr($log->user?->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">
                                    {{ $log->start_date?->format('d/m/y') }} → {{ $log->end_date?->format('d/m/y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ number_format($log->records_count) }}</span>
                                <span class="text-[10px] text-slate-400 font-bold ml-1 uppercase">Transaksi</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $log->status === 'SUCCESS' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' }}">
                                    {{ $log->status === 'SUCCESS' ? 'BERHASIL' : 'GAGAL' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-slate-200 dark:text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-slate-400 text-sm italic">Belum ada riwayat sinkronisasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>
</div>
