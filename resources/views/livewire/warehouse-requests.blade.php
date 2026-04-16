<div>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-gray-100 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#22AF85]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Warehouse Requests
                </h2>
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400 mt-1">Permintaan material produksi & tracking status fulfillment.</p>
            </div>
            <button wire:click="syncRequests" wire:loading.attr="disabled"
                class="group relative px-6 py-2.5 bg-gradient-to-r from-[#22AF85] to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all overflow-hidden disabled:opacity-50 flex items-center gap-2">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                <svg wire:loading.class="animate-spin" class="w-4 h-4 relative z-10 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span class="relative z-10 uppercase tracking-widest" wire:loading.remove wire:target="syncRequests">Sync Request</span>
                <span class="relative z-10 uppercase tracking-widest" wire:loading wire:target="syncRequests">Syncing...</span>
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-[#22AF85]/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Request</p>
                    <h3 class="text-2xl font-black text-[#22AF85]">{{ $this->totalRequests }}</h3>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Pending</p>
                    <h3 class="text-2xl font-black text-amber-500">{{ $this->pendingCount }}</h3>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Completed</p>
                    <h3 class="text-2xl font-black text-emerald-500">{{ $this->completedCount }}</h3>
                </div>
            </div>
        </div>

        {{-- Request Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-bold text-sm text-slate-800 dark:text-white uppercase tracking-widest">Daftar Permintaan Material</h3>
                <div class="flex items-center gap-3">
                    <select wire:model.live="statusFilter" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-[#22AF85] outline-none">
                        <option value="all">Semua Status</option>
                        @foreach($this->statusList as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                    <div class="relative min-w-[220px]">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari SPK..."
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-[#22AF85] dark:text-gray-200 outline-none">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400">
                            <th class="px-6 py-4">Request ID</th>
                            <th class="px-6 py-4">No. SPK</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Detail Material</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-bold">
                        @forelse($this->requests as $req)
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 text-slate-800 dark:text-white">#{{ $req->request_id }}</td>
                            <td class="px-6 py-4 text-[#22AF85] font-black uppercase">{{ $req->spk_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($req->status === 'PENDING')
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded-full border border-amber-500/20 text-[9px]">PENDING</span>
                                @elseif($req->status === 'COMPLETED')
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 text-[9px]">COMPLETED</span>
                                @else
                                    <span class="px-2 py-0.5 bg-slate-500/10 text-slate-500 rounded-full border border-slate-500/20 text-[9px]">{{ $req->status ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $req->requested_at ? $req->requested_at->format('d M Y H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-slate-400 max-w-[250px] truncate">{{ is_array($req->material_details) ? json_encode($req->material_details) : ($req->material_details ?? '-') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-50 dark:bg-amber-900/20 mb-3">
                                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                </div>
                                <h4 class="text-slate-600 dark:text-slate-300 font-bold text-xs mb-1">Belum Ada Data Request</h4>
                                <p class="text-slate-400 text-[10px] normal-case font-medium">API warehouse-request-sync belum mengirim data. Halaman ini akan otomatis terisi saat data tersedia.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
