<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
            Input Laporan Harian
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form wire:submit="save" class="p-4 sm:p-6 lg:p-8 space-y-6 md:space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                    {{-- Date Field --}}
                    <div class="space-y-2">
                        <label for="date" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Tanggal Laporan</label>
                        <input type="date" wire:model="date" id="date" 
                            class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5">
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>

                    {{-- Budgeting Field --}}
                    <div class="space-y-2">
                        <label for="budgeting" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Anggaran (Budgeting)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" wire:model="budgeting" id="budgeting" step="0.01"
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
                            <input type="number" wire:model="spent" id="spent" step="0.01"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('spent')" class="mt-2" />
                    </div>

                    {{-- Revenue Field --}}
                    <div class="space-y-2">
                        <label for="revenue" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Omset (Revenue)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" wire:model="revenue" id="revenue" step="0.01"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('revenue')" class="mt-2" />
                    </div>

                    {{-- Chat In Field --}}
                    <div class="space-y-2">
                        <label for="chat_in" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Chat Masuk</label>
                        <input type="number" wire:model="chat_in" id="chat_in"
                            class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5"
                            placeholder="0">
                        <x-input-error :messages="$errors->get('chat_in')" class="mt-2" />
                    </div>

                    {{-- Chat Consul Field --}}
                    <div class="space-y-2">
                        <label for="chat_consul" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Chat Konsul</label>
                        <input type="number" wire:model="chat_consul" id="chat_consul"
                            class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5"
                            placeholder="0">
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
