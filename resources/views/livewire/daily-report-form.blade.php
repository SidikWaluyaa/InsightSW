<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
            Input Laporan Harian
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="{ 
        formatNumber(val) {
            if (!val) return '';
            return val.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    }">
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form wire:submit="save" class="p-4 sm:p-6 lg:p-8 space-y-6 md:space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                    {{-- Date Field --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="date" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Tanggal Laporan</label>
                            <div wire:loading wire:target="date, syncApiData" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Syncing API...</span>
                            </div>
                        </div>
                        <div class="relative flex items-center gap-2">
                            <input type="date" wire:model.live="date" id="date" 
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5">
                            <button type="button" wire:click="syncApiData" class="p-2.5 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-emerald-500 hover:text-white transition-all group" title="Sync from API">
                                <svg class="w-5 h-5 group-active:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>

                    {{-- Budgeting Field --}}
                    <div class="space-y-2">
                        <label for="budgeting" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Anggaran (Budgeting)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="text" wire:model="budgeting" id="budgeting"
                                x-on:input="$event.target.value = formatNumber($event.target.value)"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('budgeting')" class="mt-2" />
                    </div>

                    {{-- Spent Field --}}
                    <div class="space-y-2">
                        <label for="spent" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Biaya Terpakai (Spent)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="text" wire:model="spent" id="spent" readonly
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12 cursor-not-allowed opacity-80"
                                placeholder="0">
                            <div wire:loading wire:target="date, syncApiData" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <div class="w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 px-1">
                            <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Termasuk PPN 11% (Base: Rp {{ number_format($rawSpent ?? 0, 0, ',', '.') }})</p>
                        </div>
                        <x-input-error :messages="$errors->get('spent')" class="mt-2" />
                    </div>

                    {{-- Revenue Field --}}
                    <div class="space-y-2">
                        <label for="revenue" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Omset (Revenue)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="text" wire:model="revenue" id="revenue" readonly
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12 cursor-not-allowed opacity-80"
                                placeholder="0">
                            <div wire:loading wire:target="date, syncApiData" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <div class="w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 px-1">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Sinkronisasi Otomatis</p>
                        </div>
                        <x-input-error :messages="$errors->get('revenue')" class="mt-2" />
                    </div>

                    {{-- Chat In Field --}}
                    <div class="space-y-2">
                        <label for="chat_in" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Chat Masuk</label>
                        <div class="relative">
                            <input type="text" wire:model="chat_in" id="chat_in" readonly
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 dark:text-gray-200 focus:ring-emerald-500 py-2.5 cursor-not-allowed opacity-80"
                                placeholder="0">
                            <div wire:loading wire:target="date, syncApiData" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <div class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('chat_in')" class="mt-2" />
                    </div>

                    {{-- Chat Consul Field --}}
                    <div class="space-y-2">
                        <label for="chat_consul" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Chat Konsul</label>
                        <div class="relative">
                            <input type="text" wire:model="chat_consul" id="chat_consul" readonly
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 dark:text-gray-200 focus:ring-emerald-500 py-2.5 cursor-not-allowed opacity-80"
                                placeholder="0">
                            <div wire:loading wire:target="date, syncApiData" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <div class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('chat_consul')" class="mt-2" />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" wire:click="resetFilters" 
                        class="px-5 md:px-6 py-2.5 md:py-3 rounded-xl md:rounded-2xl text-sm font-bold text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 transition-colors">
                        Reset
                    </button>
                    <button type="submit" 
                        class="px-6 md:px-8 py-2.5 md:py-3 rounded-xl md:rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 active:translate-y-0">
                        Simpan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
