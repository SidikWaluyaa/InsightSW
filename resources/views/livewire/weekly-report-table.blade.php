<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Laporan Agregasi Mingguan
            </h2>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-3 md:p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
            <h3 class="font-bold text-[15px] md:text-lg text-slate-800 dark:text-gray-200 tracking-tight">Pilih Periode:</h3>
            <div class="flex items-center gap-3 relative">
                <div wire:loading wire:target="selectedMonth" class="absolute -left-10 top-1/2 -translate-y-1/2">
                    <div class="w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <input type="month" wire:model.live="selectedMonth" 
                    class="rounded-lg md:rounded-xl text-sm md:text-base border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 font-medium">
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden relative">
            <div wire:loading wire:target="selectedMonth" class="absolute inset-0 z-50 bg-gray-50/50 dark:bg-gray-950/50 backdrop-blur-[2px] transition-all"></div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-extrabold text-slate-400 dark:text-gray-500">
                        <th class="px-4 md:px-6 py-4">Minggu ke-</th>
                        <th class="px-4 md:px-6 py-4">Rentang Tanggal</th>
                        <th class="px-4 md:px-6 py-4 text-right">Target Omset</th>
                        <th class="px-4 md:px-6 py-4 text-right">Total Biaya</th>
                        <th class="px-4 md:px-6 py-4 text-right">Total Omset</th>
                        <th class="px-4 md:px-6 py-4 text-center">ROAS Aktual</th>
                        <th class="px-4 md:px-6 py-4 text-center">Target ROAS</th>
                        <th class="px-4 md:px-6 py-4 text-center">Greeting Rate</th>
                        <th class="px-4 md:px-6 py-4 text-center">Cost/Chat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    @forelse ($weeks as $week)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-sm font-black">
                                    {{ $week['week'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400">{{ $week['start_date'] }}</span>
                                    <span class="text-slate-200 dark:text-slate-700">—</span>
                                    <span class="text-xs text-slate-400">{{ $week['end_date'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-gray-500 text-right">
                                Rp {{ number_format($week['target_revenue'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white text-right">
                                Rp {{ number_format($week['spent'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-emerald-600 dark:text-emerald-400 text-right">
                                Rp {{ number_format($week['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black {{ $week['roas_color'] === 'green' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($week['roas_color'] === 'yellow' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400') }}">
                                    {{ number_format($week['roas'], 2) }}x
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-400">
                                {{ number_format($week['target_roas'], 2) }}x
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-violet-50 dark:bg-violet-500/5 rounded-lg border border-violet-100 dark:border-violet-500/10">
                                    <span class="text-xs font-bold text-violet-600 dark:text-violet-400">{{ $week['greeting_rate'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-gray-300">Rp {{ number_format($week['cost_per_chat_consul'], 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center italic text-slate-400">Belum ada data untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
