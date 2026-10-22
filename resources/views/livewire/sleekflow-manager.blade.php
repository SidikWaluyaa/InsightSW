<div wire:poll.10s="checkSync"
     x-data="{ 
        seconds: 0,
        lastSync: @entangle('lastSyncTimestamp'),
        isSyncing: @entangle('isSyncing'),
        serverTime: {{ time() }},
        browserTime: Math.floor(Date.now() / 1000),
        get drift() { return this.serverTime - this.browserTime; },
        init() {
            setInterval(() => {
                if (this.lastSync) {
                    let now = Math.floor(Date.now() / 1000) + this.drift;
                    this.seconds = Math.max(0, 60 - (now - this.lastSync));
                }
            }, 1000);
        }
     }"
     class="space-y-6">

    {{-- Page Header --}}
    <x-ui.page-header title="Chat Masuk" subtitle="Kontak Sleekflow per tanggal masuk, dengan status chat dan jeda balasan." badge="Live">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="now()->toDateString()" :presets="$this->datePresets()" refresh="sync" refresh-label="Tarik Data" />
    </x-ui.page-header>

    {{-- Range Info & Live Sync Status --}}
    <x-ui.range-info :start="$startDate" :end="$endDate">
        <template x-if="isSyncing">
            <span class="inline-flex items-center gap-2 font-semibold text-indigo-400 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                Sinkronisasi kontak…
            </span>
        </template>
        <template x-if="!isSyncing && seconds > 0 && $wire.startDate === '{{ now()->format('Y-m-d') }}'">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Pembaruan otomatis dalam <span x-text="seconds" class="font-mono font-bold text-indigo-400"></span> detik</span>
            </span>
        </template>
    </x-ui.range-info>

    {{-- KPI Metric Cards (Uniform 6-card grid matching Analitik Sleekflow) --}}
    @php
        $kpiList = [
            [
                'label' => 'Total Kontak',
                'value' => number_format($contacts->total()),
                'note' => 'Semua kontak masuk',
                'glow' => 'bg-blue-500/10',
                'iconBg' => 'bg-blue-500/20 text-blue-400',
                'iconPath' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            ],
            [
                'label' => 'Greeting',
                'value' => number_format($totalGreeting),
                'note' => 'Tahap perkenalan awal',
                'glow' => 'bg-cyan-500/10',
                'iconBg' => 'bg-cyan-500/20 text-cyan-400',
                'iconPath' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
            ],
            [
                'label' => 'Konsultasi',
                'value' => number_format($totalKonsul),
                'note' => 'Tahap konsultasi & diskusi',
                'glow' => 'bg-emerald-500/10',
                'iconBg' => 'bg-emerald-500/20 text-emerald-400',
                'iconPath' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
            ],
            [
                'label' => 'Closing',
                'value' => number_format($totalClosing),
                'note' => 'Berhasil konversi penjualan',
                'glow' => 'bg-amber-500/10',
                'iconBg' => 'bg-amber-500/20 text-amber-400',
                'iconPath' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Conversion Rate',
                'value' => $greetingToKonsulRate . '%',
                'note' => 'Greeting ke Konsultasi',
                'glow' => 'bg-purple-500/10',
                'iconBg' => 'bg-purple-500/20 text-purple-400',
                'iconPath' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            ],
            [
                'label' => 'Unhandled Chat',
                'value' => number_format($unhandledCount),
                'note' => 'Chat belum terbalas',
                'glow' => 'bg-rose-500/10',
                'iconBg' => 'bg-rose-500/20 text-rose-400',
                'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            ],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach($kpiList as $card)
            <div wire:key="sf-kpi-{{ Str::slug($card['label']) }}" class="bg-white dark:bg-[#1e2336] rounded-2xl p-5 border border-gray-100 dark:border-white/5 relative overflow-hidden group flex flex-col shadow-sm transition-all hover:border-white/10">
                <div class="absolute top-0 right-0 w-24 h-24 {{ $card['glow'] }} rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between gap-2 relative z-10 mb-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight truncate">{{ $card['label'] }}</span>
                    <div class="shrink-0 w-8 h-8 rounded-full {{ $card['iconBg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['iconPath'] }}" />
                        </svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">{{ $card['value'] }}</h3>
                </div>
                <p class="relative z-10 mt-3 pt-3 border-t border-gray-100 dark:border-white/5 text-[10px] leading-snug text-slate-400 truncate">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filter Toolbar: Search, Jeda Balasan, Status Chat --}}
    <div class="bg-white dark:bg-[#1e2336] rounded-2xl p-4 border border-gray-100 dark:border-white/5 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Search Input --}}
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kontak (Nama, No Telepon, Email)..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-[#151928] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
        </div>

        {{-- Filters Group --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {{-- Jeda Balasan --}}
            <select wire:change="setGapFilter($event.target.value)" aria-label="Jeda balasan"
                class="h-10 px-3.5 pr-8 bg-gray-50 dark:bg-[#151928] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <option value="">Jeda balasan: semua</option>
                <option value="1" @selected($gapFilter == '1')>&gt; 1 hari</option>
                <option value="3" @selected($gapFilter == '3')>&gt; 3 hari</option>
                <option value="7" @selected($gapFilter == '7')>&gt; 7 hari</option>
            </select>

            {{-- Status Chat --}}
            <select wire:change="setStatusFilter($event.target.value)" aria-label="Status chat"
                class="h-10 px-3.5 pr-8 bg-gray-50 dark:bg-[#151928] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <option value="">Status: semua</option>
                @foreach($uniqueStatuses as $status)
                    <option value="{{ $status }}" @selected($statusFilter == $status)>{{ $status }}</option>
                @endforeach
            </select>

            @if($search || $gapFilter || $statusFilter)
                <button type="button" wire:click="$set('search', ''); $wire.setGapFilter(''); $wire.setStatusFilter('');"
                    class="h-10 px-3 inline-flex items-center gap-1.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 text-xs font-bold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span>Reset</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Main Table Area --}}
    <div class="bg-white dark:bg-[#1e2336] rounded-2xl border border-gray-100 dark:border-white/5 overflow-hidden shadow-sm">
        {{-- Table Title Bar --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Daftar Kontak Sleekflow</h2>
            </div>
            <span class="text-xs font-semibold text-slate-400">
                Menampilkan <strong class="text-slate-800 dark:text-slate-200">{{ number_format($contacts->total()) }}</strong> kontak
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-[#151928] text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-gray-100 dark:border-white/5">
                        <th class="px-6 py-4">Nama & Kontak</th>
                        <th class="px-6 py-4">Status Chat</th>
                        <th class="px-6 py-4">Customer Sent</th>
                        <th class="px-6 py-4">You Sent</th>
                        <th class="px-6 py-4">Owner / Team</th>
                        <th class="px-6 py-4">Stage / Source</th>
                        <th class="px-6 py-4">Dibuat (WIB)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($contacts as $contact)
                        <tr wire:key="sf-contact-{{ $contact->id }}" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                            {{-- Nama & Kontak --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/15 to-blue-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-black text-xs shrink-0">
                                        {{ substr($contact->first_name ?: 'C', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight truncate">
                                            {{ $contact->first_name }} {{ $contact->last_name }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-indigo-400 font-mono tracking-tight mt-0.5">
                                            {{ $contact->phone_number }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Chat --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->status_chat)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {{ $contact->status_chat }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-600">—</span>
                                @endif
                            </td>

                            {{-- Customer Sent --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->last_contact_from_customers)
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $contact->last_contact_from_customers->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono mt-0.5 pl-3">
                                            {{ $contact->last_contact_from_customers->format('H:i') }} WIB
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-600">—</span>
                                @endif
                            </td>

                            {{-- You Sent --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $lastYou = $contact->last_contacted_from_company ?? $contact->last_contacted_from_user;
                                    $dotClass = 'bg-slate-400';
                                    
                                    if ($lastYou) {
                                        $hours = $lastYou->diffInHours(now());
                                        if ($hours < 2) {
                                            $dotClass = 'bg-emerald-400';
                                        } elseif ($hours < 8) {
                                            $dotClass = 'bg-amber-400';
                                        } elseif ($hours < 24) {
                                            $dotClass = 'bg-orange-400';
                                        } else {
                                            $dotClass = 'bg-rose-400 animate-pulse';
                                        }
                                    }
                                @endphp
                                @if($lastYou)
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} shrink-0"></span>
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $lastYou->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono mt-0.5 pl-3">
                                            {{ $lastYou->format('H:i') }} WIB
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-600">—</span>
                                @endif
                            </td>

                            {{-- Owner / Team --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $contact->contact_owner_name ?: 'Unassigned' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">
                                        {{ $contact->assigned_team ?: '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Stage / Source --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 uppercase">
                                        {{ $contact->lifecycle_stage ?: '-' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        Src: {{ $contact->lead_source ?: '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Dibuat (WIB) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $contact->created_at_sleekflow->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        {{ $contact->created_at_sleekflow->format('H:i:s') }} WIB
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tidak ada data kontak ditemukan</p>
                                    <p class="text-[11px] text-slate-500">Coba ubah kata kunci pencarian atau rentang tanggal.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($contacts->hasPages())
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-[#151928] border-t border-gray-100 dark:border-white/5">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>
