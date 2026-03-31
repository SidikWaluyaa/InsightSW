<div class="px-6 py-8" wire:poll.30s="autoSync">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Dashboard Performa CS</h1>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-tighter">LIVE</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-0.5">
                        Aktif Setiap 30 Detik • Update: {{ $lastSynced->diffForHumans() }}
                    </p>
                </div>
            </div>

            {{-- Filter Tanggal --}}
            <div class="flex items-center bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-1 shadow-sm transition-colors duration-300">
                <input type="date" id="start_date" value="{{ $startDate }}" 
                    onchange="Livewire.dispatch('set-date-filters', { start: this.value, end: document.getElementById('end_date').value })" 
                    class="bg-transparent border-none text-[10px] font-bold text-slate-600 dark:text-gray-300 focus:ring-0">
                <span class="text-slate-300 dark:text-gray-700 px-1">—</span>
                <input type="date" id="end_date" value="{{ $endDate }}" 
                    onchange="Livewire.dispatch('set-date-filters', { start: document.getElementById('start_date').value, end: this.value })" 
                    class="bg-transparent border-none text-[10px] font-bold text-slate-600 dark:text-gray-300 focus:ring-0">
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        {{-- Total Greeting --}}
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[32px] p-6 shadow-xl shadow-indigo-100/20 dark:shadow-none hover:translate-y-[-4px] transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Greeting</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalGreeting) }}</h3>
                </div>
            </div>
        </div>

        {{-- Total Konsul --}}
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[32px] p-6 shadow-xl shadow-slate-200/50 dark:shadow-none hover:translate-y-[-4px] transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Konsul</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalKonsul) }}</h3>
                </div>
            </div>
        </div>

        {{-- Total Closing --}}
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[32px] p-6 shadow-xl shadow-slate-200/50 dark:shadow-none hover:translate-y-[-4px] transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Closing</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalClosing) }}</h3>
                </div>
            </div>
        </div>

        {{-- Conversion Rate --}}
        <div class="bg-indigo-600 rounded-[32px] p-6 shadow-xl shadow-indigo-500/20 hover:translate-y-[-4px] transition-all duration-300">
            <p class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">Greeting to Konsul</p>
            <div class="flex items-end justify-between">
                <h3 class="text-3xl font-black text-white">{{ $greetingToKonsulRate }}<span class="text-lg ml-1 opacity-60">%</span></h3>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="mt-4 bg-white/10 rounded-full h-1.5 overflow-hidden">
                <div class="bg-white h-full transition-all duration-500" style="width: {{ $greetingToKonsulRate }}%"></div>
            </div>
        </div>

        {{-- Unhandled --}}
        <div class="bg-rose-600 rounded-[32px] p-6 shadow-xl shadow-rose-500/20 hover:translate-y-[-4px] transition-all duration-300">
            <p class="text-[10px] font-black uppercase tracking-widest text-white/60 mb-2">Gak Kehandle (Kosong)</p>
            <div class="flex items-end justify-between">
                <div>
                    <h3 class="text-3xl font-black text-white">{{ number_format($unhandledCount) }}<span class="text-xs ml-2 font-black opacity-60">CHAT</span></h3>
                    <p class="text-[10px] font-bold text-white/50 mt-1 uppercase tracking-widest">Rate: {{ $unhandledRate }}%</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="mt-4 bg-white/10 rounded-full h-1.5 overflow-hidden">
                <div class="bg-white h-full transition-all duration-500" style="width: {{ $unhandledRate }}%"></div>
            </div>
        </div>
    </div>
    
    {{-- Team Leaderboard --}}
    <div class="mt-8">
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[32px] overflow-hidden shadow-xl shadow-slate-200/50 dark:shadow-none transition-all duration-300">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">🏆 Leaderboard Performa Tim CS</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Peringkat berdasarkan jumlah Closing terbanyak</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30 text-[10px] uppercase font-black text-slate-400 dark:text-gray-500 tracking-[0.2em]">
                            <th class="px-8 py-5">Nama Owner / CS</th>
                            <th class="px-6 py-5">Greeting</th>
                            <th class="px-6 py-5">Konsul</th>
                            <th class="px-6 py-5 text-center">Conv. Rate</th>
                            <th class="px-6 py-5 text-center">Closing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-bold">
                        @forelse($ownerStats as $stat)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-all duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-200 to-slate-100 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-400 font-black text-sm shadow-sm">
                                            {{ substr($stat->display_name ?: 'C', 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-800 dark:text-white leading-tight">
                                                {{ $stat->display_name }}
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Total Assigned: {{ $stat->total_contacts }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-slate-600 dark:text-gray-300 text-xs">
                                    {{ number_format($stat->total_greeting) }}
                                </td>
                                <td class="px-6 py-5 text-slate-600 dark:text-gray-300 text-xs">
                                    {{ number_format($stat->total_konsul) }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="px-2.5 py-1 rounded-lg {{ $stat->conversion_rate > 20 ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20' }} text-[10px] font-black border">
                                            {{ $stat->conversion_rate }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center font-black">
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/20 text-xs">
                                        {{ number_format($stat->total_closing) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <p class="text-sm font-bold text-slate-400">Belum ada data owner untuk periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Info Section --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[32px] p-8">
            <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Informasi Dashboard</h4>
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-6 h-6 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-500 shrink-0">
                        <span class="text-[10px] font-bold">1</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-gray-400 leading-relaxed">
                        Data diambil dari sinkronisasi Sleekflow. Gunakan filter tanggal di atas untuk memantau performa periode tertentu.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
