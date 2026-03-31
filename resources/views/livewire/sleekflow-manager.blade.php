<div>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                    Chat Masuk (Sleekflow)
                </h2>
                <p class="text-xs font-bold text-slate-400 dark:text-gray-500 mt-1 uppercase tracking-widest text-indigo-500">Customer Service • Real-time Sync</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-1 shadow-sm transition-colors duration-300">
                    <select onchange="Livewire.dispatch('set-status-filter', { status: this.value })" 
                        class="bg-transparent border-none text-[10px] font-black text-slate-600 dark:text-gray-300 focus:ring-0 cursor-pointer pr-8 uppercase tracking-widest appearance-none">
                        <option value="" class="bg-white text-slate-900 dark:bg-slate-900 dark:text-white uppercase font-bold">Semua Status</option>
                        @foreach($uniqueStatuses as $status)
                            <option value="{{ $status }}" {{ $statusFilter == $status ? 'selected' : '' }} 
                                class="bg-white text-slate-900 dark:bg-slate-900 dark:text-white uppercase font-bold">
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-1 shadow-sm transition-colors duration-300">
                    <input type="date" id="start_date" value="{{ $startDate }}" 
                        onchange="Livewire.dispatch('set-date-filters', { start: this.value, end: document.getElementById('end_date').value })" 
                        class="bg-transparent border-none text-[10px] font-bold text-slate-600 dark:text-gray-300 focus:ring-0">
                    <span class="text-slate-300 dark:text-gray-700 px-1">—</span>
                    <input type="date" id="end_date" value="{{ $endDate }}" 
                        onchange="Livewire.dispatch('set-date-filters', { start: document.getElementById('start_date').value, end: this.value })" 
                        class="bg-transparent border-none text-[10px] font-bold text-slate-600 dark:text-gray-300 focus:ring-0">
                </div>
                
                <button onclick="
                    Swal.fire({
                        title: 'Tarik Data Sleekflow',
                        text: 'Sedang menyisir seluruh data kontak dari API... Mohon tunggu.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    Livewire.dispatch('sync-sleekflow');
                " wire:loading.attr="disabled" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2.5 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 text-sm active:scale-95 group">
                    <div wire:loading.remove wire:target="sync" class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-180 transition-transform duration-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </div>
                    <div wire:loading wire:target="sync" class="w-6 h-6 flex items-center justify-center">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <span>{{ $isSyncing ? 'Sinkronisasi...' : 'Tarik Data' }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 py-8 space-y-6">

        {{-- Search and Meta Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3 bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 flex items-center gap-4 transition-all duration-300">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kontak (Nama, No Telepon, Email)..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 dark:bg-gray-800/30 border border-transparent focus:border-indigo-500 rounded-2xl text-sm focus:ring-0 dark:text-gray-200 transition-all">
                </div>
            </div>
            
            <div class="bg-indigo-600 p-6 rounded-[2.5rem] shadow-lg shadow-indigo-500/20 text-white flex flex-col justify-center transition-all duration-300">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Total Kontak</span>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-2xl font-black">{{ number_format($contacts->total()) }}</span>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact Stats Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- Greeting --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between hover:border-indigo-500/30 transition-colors group">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-indigo-500 transition-colors">Greeting</span>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($totalGreeting) }}</h3>
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                </div>
            </div>
            {{-- Konsul --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between hover:border-emerald-500/30 transition-colors group">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-emerald-500 transition-colors">Konsultasi</span>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($totalKonsul) }}</h3>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    </div>
                </div>
            </div>
            {{-- Closing --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between hover:border-amber-500/30 transition-colors group">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-amber-500 transition-colors">Closing</span>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ number_format($totalClosing) }}</h3>
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            {{-- Conversion --}}
            <div class="bg-indigo-600 rounded-3xl p-4 shadow-lg shadow-indigo-500/20 flex flex-col justify-between hover:scale-[1.02] transition-all">
                <span class="text-[9px] font-black uppercase tracking-widest text-white/60">Conversion Rate</span>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-xl font-black text-white">{{ $greetingToKonsulRate }}%</h3>
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>
            </div>
            {{-- Unhandled --}}
            <div class="bg-rose-600 rounded-3xl p-4 shadow-lg shadow-rose-500/20 flex flex-col justify-between hover:scale-[1.02] transition-all">
                <span class="text-[9px] font-black uppercase tracking-widest text-white/60">Unhandled Chat</span>
                <div class="flex items-end justify-between mt-1">
                    <h3 class="text-xl font-black text-white">{{ number_format($unhandledCount) }}</h3>
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table Area --}}
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30 text-[10px] uppercase font-black text-slate-400 dark:text-gray-500 tracking-[0.2em]">
                            <th class="px-8 py-5">Nama & Kontak</th>
                            <th class="px-6 py-5">Status Chat</th>
                            <th class="px-6 py-5">Owner / Team</th>
                            <th class="px-6 py-5">Stage / Source</th>
                            <th class="px-6 py-5">Dibuat (WIB)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-all duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center text-white font-black text-sm shadow-md shadow-indigo-500/10">
                                            {{ substr($contact->first_name ?: 'C', 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-white leading-tight">
                                                {{ $contact->first_name }} {{ $contact->last_name }}
                                            </span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter">{{ $contact->phone_number }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($contact->status_chat)
                                        <span class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-200/50 dark:border-emerald-500/20">
                                            {{ $contact->status_chat }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-gray-300">{{ $contact->contact_owner_name ?: 'Unassigned' }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $contact->assigned_team ?: '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-black text-indigo-500 uppercase">{{ $contact->lifecycle_stage ?: '-' }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 italic">Source: {{ $contact->lead_source ?: '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-gray-300">{{ $contact->created_at_sleekflow->format('d M Y') }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $contact->created_at_sleekflow->format('H:i:s') }} WIB</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-gray-800 flex items-center justify-center text-slate-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-400 dark:text-gray-500">Tidak ada data kontak ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($contacts->hasPages())
                <div class="px-8 py-4 bg-gray-50/50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
