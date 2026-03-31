<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
            Pengaturan Target Bulanan
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden relative">
            {{-- Decorative Background --}}
            <div class="absolute top-0 right-0 p-8 opacity-5">
                <svg class="w-32 h-32 text-slate-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
            </div>

            <form wire:submit="save" class="p-4 sm:p-6 lg:p-8 space-y-6 md:space-y-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                    {{-- Month Selection --}}
                    <div class="space-y-2 md:col-span-2">
                        <label for="month" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Pilih Bulan & Tahun</label>
                        <input type="month" wire:model.live="month" id="month" 
                            class="w-full md:w-1/2 rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 font-bold text-base md:text-lg">
                        <x-input-error :messages="$errors->get('month')" class="mt-2" />
                        <p class="text-xs text-slate-400">Target akan diatur khusus untuk bulan yang dipilih di atas.</p>
                    </div>

                    {{-- Budget Field --}}
                    <div class="space-y-2">
                        <label for="budget" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Anggaran Iklan (Budget)</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-emerald-500 transition-colors">Rp</span>
                            <input type="number" wire:model="budget" id="budget" step="0.01"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12 font-bold"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('budget')" class="mt-2" />
                    </div>

                    {{-- Target ROAS Field --}}
                    <div class="space-y-2">
                        <label for="target_roas" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Target ROAS (Multiplikasi)</label>
                        <div class="relative group">
                            <input type="number" wire:model="target_roas" id="target_roas" step="0.1"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pr-10 font-bold"
                                placeholder="0.0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">x</span>
                        </div>
                        <x-input-error :messages="$errors->get('target_roas')" class="mt-2" />
                    </div>

                    {{-- Target Revenue Field --}}
                    <div class="space-y-2">
                        <label for="target_revenue" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Target Omset (Revenue)</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-emerald-500 transition-colors">Rp</span>
                            <input type="number" wire:model="target_revenue" id="target_revenue" step="0.01"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pl-12 font-bold"
                                placeholder="0">
                        </div>
                        <x-input-error :messages="$errors->get('target_revenue')" class="mt-2" />
                    </div>

                    {{-- Calendar Control Section --}}
                    <div class="md:col-span-2 pt-6 mt-2 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center text-violet-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-gray-200">Pengaturan Kalender Kerja</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                            {{-- Total Days Field --}}
                            <div class="space-y-2">
                                <label for="total_days" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Total Hari (Bulan Ini)</label>
                                <div class="relative group">
                                    <input type="number" wire:model="total_days" id="total_days"
                                        class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pr-16 font-bold"
                                        readonly>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold uppercase tracking-widest">Hari</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Ditetapkan otomatis berdasarkan kalender standar.</p>
                                <x-input-error :messages="$errors->get('total_days')" class="mt-2" />
                            </div>

                            {{-- Total Holidays Field --}}
                            <div class="space-y-2">
                                <label for="total_holidays" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Total Hari Libur</label>
                                <div class="relative group">
                                    <input type="number" wire:model.live="total_holidays" id="total_holidays" min="0" max="25"
                                        class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pr-16 font-bold"
                                        placeholder="0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold uppercase tracking-widest">Hari</span>
                                </div>
                                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">
                                    Total Hari Kerja Efektif: <span class="font-bold text-lg">{{ (int)$total_days - (int)$total_holidays }}</span> Hari
                                </p>
                                <x-input-error :messages="$errors->get('total_holidays')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Target Chat Field --}}
                    <div class="space-y-2">
                        <label for="target_chat_consul" class="block text-sm font-bold text-slate-700 dark:text-gray-300">Target Chat Konsul</label>
                        <div class="relative group">
                            <input type="number" wire:model="target_chat_consul" id="target_chat_consul"
                                class="w-full rounded-xl md:rounded-2xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-emerald-500 py-2.5 pr-16 font-bold"
                                placeholder="0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold uppercase tracking-widest">Chat</span>
                        </div>
                        <x-input-error :messages="$errors->get('target_chat_consul')" class="mt-2" />
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="text-sm text-slate-400 max-w-xs italic">
                        Perubahan target akan secara otomatis mengupdate indikator warna pada dasbor.
                    </div>
                    <button type="submit" 
                        class="px-8 md:px-10 py-3 md:py-4 rounded-xl md:rounded-2xl bg-gradient-to-r from-slate-800 to-slate-900 dark:from-emerald-600 dark:to-teal-700 text-white text-sm font-bold shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1 active:translate-y-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
