<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
            Manajemen Transfer Saldo
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-8">
        {{-- Transfer Form --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-4 md:p-6 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-bold text-base md:text-lg text-slate-800 dark:text-white">Tambah Transfer Baru</h3>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-bold">Gerakkan Saldo Antar Kampanye</p>
                </div>
                <form wire:submit="save" class="p-4 md:p-6 space-y-4 md:space-y-6">
                    <div class="space-y-2">
                        <label for="date" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Tanggal</label>
                        <input type="date" wire:model="date" id="date" 
                            class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5">
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <label for="amount" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Nominal</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" wire:model="amount" id="amount" step="0.01"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Catatan / Alasan</label>
                        <textarea wire:model="description" id="description" rows="3"
                            class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5"
                            placeholder="Contoh: Dari Campaign A ke Campaign B"></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <button type="submit" 
                        class="w-full py-3 md:py-4 rounded-xl md:rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        Simpan Transfer
                    </button>
                    
                    @if (session('success'))
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold text-center animate-bounce">
                            {{ session('success') }}
                        </div>
                    @endif
                </form>
            </div>

            {{-- Summary Card --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-xl border border-slate-700/50">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Total Transfer Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                </div>
                <div class="text-2xl md:text-3xl font-black text-white leading-none mb-2">
                    Rp {{ number_format($totalTransfers, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-400">Akumulasi seluruh pemindahan saldo anggaran.</p>
            </div>
        </div>

        {{-- History Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-4 md:p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                    <h3 class="font-bold text-base md:text-lg text-slate-800 dark:text-white">Riwayat Transfer Seluruhnya</h3>
                    <input type="month" wire:model.live="selectedMonth" 
                        class="rounded-lg md:rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 text-[11px] md:text-xs py-1.5 md:py-2">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Nominal</th>
                                <th class="px-6 py-4">Keterangan / Catatan</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($transfers as $transfer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium text-slate-600 dark:text-gray-300">
                                        {{ $transfer->date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($transfer->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm text-slate-500 dark:text-gray-400 italic">
                                        "{{ $transfer->note ?: '-' }}"
                                    </td>
                                    <td class="px-4 md:px-6 py-3 md:py-4 text-right">
                                        <button wire:click="delete({{ $transfer->id }})" 
                                            class="p-2 rounded-lg text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                                            onclick="confirm('Apakah Anda yakin ingin menghapus data transfer ini?') || event.stopImmediatePropagation()">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="w-12 h-12 text-gray-200 dark:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <p class="text-slate-400 dark:text-gray-500 text-sm italic">Belum ada riwayat transfer saldo.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
