<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Shoe Workshop - Marketing Analytics') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300" x-data="{ open: true, mobileOpen: false }">

            {{-- Mobile Overlay --}}
            <div x-show="mobileOpen" @click="mobileOpen = false" x-transition.opacity class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

            {{-- Sidebar --}}
            <aside 
                class="fixed inset-y-0 left-0 z-[70] flex flex-col transition-all duration-300 transform lg:translate-x-0"
                :class="{
                    'w-60': open,
                    'w-20': !open,
                    '-translate-x-full': !mobileOpen,
                    'translate-x-0': mobileOpen
                }">

                {{-- Sidebar Background --}}
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 border-r border-slate-700/50"></div>

                {{-- Logo --}}
                <div class="relative flex items-center h-24 px-4 mb-4">
                    <div class="flex items-center gap-4 overflow-hidden group">
                        {{-- Branded Icon (Extracted Graphic) --}}
                        <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center p-1">
                            <img src="{{ asset('assets/logo-icon.png') }}" 
                                 class="w-full h-full object-contain filter drop-shadow-[0_0_8px_rgba(52,211,153,0.3)] transition-transform duration-500 group-hover:scale-110" 
                                 alt="Shoe Workshop Icon">
                        </div>
                        
                        {{-- Branded Text --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex flex-col">
                            <span class="text-white font-black text-[11px] uppercase tracking-[0.4em] leading-tight mb-0.5">SHOE</span>
                            <span class="text-emerald-400 font-black text-[11px] uppercase tracking-[0.4em] leading-tight">WORKSHOP</span>
                            <div class="w-8 h-0.5 bg-emerald-500/50 mt-1 rounded-full"></div>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="relative flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    @php
                        $subItems = [
                            ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />', 'roles' => ['Admin', 'Editor', 'Viewer']],
                            ['route' => 'daily-report', 'label' => 'Laporan Harian', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />', 'roles' => ['Admin', 'Editor']],
                            ['route' => 'weekly-report', 'label' => 'Laporan Mingguan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />', 'roles' => ['Admin', 'Editor']],
                            ['route' => 'budget-transfer', 'label' => 'Transfer Saldo', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />', 'roles' => ['Admin', 'Editor']],
                            ['route' => 'meta-ads', 'label' => 'Meta Ads', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />', 'roles' => ['Admin', 'Editor', 'Viewer']],
                            ['route' => 'monthly-settings', 'label' => 'Pengaturan Target', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />', 'roles' => ['Admin']],
                        ];

                        // Filter subItems based on user roles
                        $subItems = array_filter($subItems, function($item) {
                            return auth()->user()->hasRole($item['roles']);
                        });
                        
                        $isMarketingActive = request()->routeIs(['dashboard', 'daily-report', 'weekly-report', 'budget-transfer', 'meta-ads', 'monthly-settings']);
                    @endphp

                    {{-- Marketing (Collapsible Container) --}}
                    @can('access-marketing')
                    <div x-data="{ marketingOpen: {{ $isMarketingActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="marketingOpen = !marketingOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isMarketingActive ? 'bg-gradient-to-r from-indigo-500/10 to-blue-500/10 text-indigo-400 border border-indigo-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px]">Marketing</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': marketingOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Sub-Navigation Menu --}}
                        <div x-show="marketingOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            x-transition:enter-start="opacity-0 -translate-y-4" 
                            x-transition:enter-end="opacity-100 translate-y-0" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            
                            @foreach ($subItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-indigo-500/20 shadow-lg shadow-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-indigo-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @endcan
                    
                    {{-- Customer Service (Collapsible Container) --}}
                    @can('access-cs')
                    @php
                        $csSubItems = [
                            ['route' => 'cs-dashboard', 'label' => 'Dashboard', 'icon' => '...'],
                            ['route' => 'chat-masuk', 'label' => 'Chat Masuk', 'icon' => '...'],
                            ['route' => 'cs-followup', 'label' => 'Followup', 'icon' => '...'],
                            ['route' => 'cs-tracking', 'label' => 'Tracking', 'icon' => '...'],
                        ];

                        // Filter Followup for Leader CS/Admin/Editor only
                        $csSubItems = array_filter($csSubItems, function($item) {
                            if ($item['route'] === 'cs-followup') {
                                return auth()->user()->can('access-cs-followup');
                            }
                            return true;
                        });

                        $isCsActive = request()->routeIs(['cs-dashboard', 'chat-masuk', 'cs-followup', 'cs-tracking']);
                    @endphp
                    <div x-data="{ csOpen: {{ $isCsActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="csOpen = !csOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isCsActive ? 'bg-gradient-to-r from-indigo-500/10 to-blue-500/10 text-indigo-400 border border-indigo-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">CS</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': csOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="csOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($csSubItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-indigo-500/20 shadow-lg shadow-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-indigo-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Customer Experience (Collapsible Container) --}}
                    @can('access-cx')
                    @php
                        $cxSubItems = [
                            ['route' => 'cx-upsell', 'label' => 'Upsell Report', 'icon' => '...'],
                            ['route' => 'quality-control', 'label' => 'AFTER', 'icon' => '...'],
                            ['route' => 'cx-konfirmasi-after', 'label' => 'Konfirmasi After', 'icon' => '...'],
                        ];
                        $isCxActive = request()->routeIs(['cx-upsell', 'quality-control', 'cx-konfirmasi-after']);
                    @endphp
                    <div x-data="{ cxOpen: {{ $isCxActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="cxOpen = !cxOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isCxActive ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 text-emerald-400 border border-emerald-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">CX</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': cxOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="cxOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($cxSubItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-emerald-500/20 shadow-lg shadow-emerald-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-emerald-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Finance (Collapsible Container) --}}
                    @can('access-finance')
                    @php
                        $financeSubItems = [
                            ['route' => 'finance-sync', 'label' => 'Financial Dashboard', 'icon' => '...'],
                            ['route' => 'finance-payment-insights', 'label' => 'Payment Insights', 'icon' => '...'],
                            ['route' => 'finance-history', 'label' => 'Riwayat Sync', 'icon' => '...'],
                        ];
                        $isFinanceActive = request()->routeIs(['finance-sync', 'finance-payment-insights', 'finance-history']);
                    @endphp
                    <div x-data="{ financeOpen: {{ $isFinanceActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="financeOpen = !financeOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isFinanceActive ? 'bg-gradient-to-r from-indigo-500/10 to-blue-500/10 text-indigo-400 border border-indigo-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">Finance</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': financeOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="financeOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($financeSubItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-indigo-500/20 shadow-lg shadow-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-indigo-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Gudang (Collapsible Container) --}}
                    @can('access-gudang')
                    @php
                        $gudangSubItems = [
                            ['route' => 'warehouse-command-center', 'label' => 'Pusat Komando'],
                        ];
                        $isGudangActive = request()->routeIs(['warehouse-command-center']);
                    @endphp
                    <div x-data="{ gudangOpen: {{ $isGudangActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="gudangOpen = !gudangOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isGudangActive ? 'bg-gradient-to-r from-[#22AF85]/10 to-teal-500/10 text-[#22AF85] border border-[#22AF85]/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#22AF85]/10 flex items-center justify-center text-[#22AF85]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">Gudang</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': gudangOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="gudangOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($gudangSubItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-[#22AF85]/20 shadow-lg shadow-[#22AF85]/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-[#22AF85] scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Workshop (Collapsible Container) --}}
                    @can('access-workshop')
                    @php
                        $workshopSubItems = [
                            ['route' => 'workshop-intelligence-v2', 'label' => 'Workshop Intelligence v2'],
                        ];
                        $isWorkshopActive = request()->routeIs(['workshop-intelligence-v2']);
                    @endphp
                    <div x-data="{ workshopOpen: {{ $isWorkshopActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="workshopOpen = !workshopOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isWorkshopActive ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 text-emerald-400 border border-emerald-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">Workshop</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': workshopOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="workshopOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($workshopSubItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-emerald-500/20 shadow-lg shadow-emerald-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-emerald-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Supply Chain (Collapsible Container) --}}
                    @can('access-supply-chain')
                    @php
                        $supplyChainItems = [
                            ['route' => 'warehouse-dashboard', 'label' => 'Inventori & Analitik'],
                            ['route' => 'warehouse-requests', 'label' => 'Permintaan Material'],
                            ['route' => 'warehouse-transactions', 'label' => 'Riwayat Transaksi'],
                            ['route' => 'warehouse-intelligence', 'label' => 'Audit & Prediksi'],
                        ];
                        $isSupplyChainActive = request()->routeIs(['warehouse-dashboard', 'warehouse-requests', 'warehouse-transactions', 'warehouse-intelligence']);
                    @endphp
                    <div x-data="{ scOpen: {{ $isSupplyChainActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="scOpen = !scOpen"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ $isSupplyChainActive ? 'bg-gradient-to-r from-indigo-500/10 to-blue-500/10 text-indigo-400 border border-indigo-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px] font-black">Supply Chain</span>
                            </div>
                            <svg x-show="open" class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': scOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="scOpen && open" 
                            x-transition:enter="transition ease-out duration-300" 
                            class="pl-4 pr-2 py-2 space-y-1">
                            @foreach ($supplyChainItems as $item)
                                <a href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition-all duration-200 group
                                    {{ request()->routeIs($item['route']) ? 'text-white bg-indigo-500/20 shadow-lg shadow-indigo-500/10' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                                    <div class="w-1.5 h-1.5 rounded-full transition-all duration-300 {{ request()->routeIs($item['route']) ? 'bg-indigo-400 scale-125' : 'bg-slate-700 group-hover:bg-slate-400' }}"></div>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    {{-- Manajemen Pengguna (Standalone) --}}
                    @can('manage-users')
                    <div class="space-y-1">
                        <a href="{{ route('users') }}" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200
                            {{ request()->routeIs('users') ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 text-emerald-400 border border-emerald-400/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg {{ request()->routeIs('users') ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700/30 text-slate-400 group-hover:text-white transition-colors' }} flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <span x-show="open" x-transition class="uppercase tracking-widest text-[11px]">Akun</span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </nav>

                {{-- Bottom Section --}}
                <div class="relative px-3 py-4 border-t border-slate-700/50 space-y-2">
                    {{-- Dark Mode Toggle --}}
                    <button @click="darkMode = !darkMode"
                        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                        <template x-if="darkMode">
                            <svg class="w-5 h-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </template>
                        <template x-if="!darkMode">
                            <svg class="w-5 h-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </template>
                        <span x-show="open" x-transition x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
                    </button>

                    {{-- Sidebar Toggle (Desktop Only) --}}
                    <button @click="open = !open"
                        class="hidden lg:flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                        <svg class="w-5 h-5 shrink-0 transition-transform duration-300" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <span x-show="open" x-transition>Sembunyikan</span>
                    </button>

                    {{-- User Menu --}}
                    @auth
                    <div class="flex items-center gap-3 px-3 py-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div x-show="open" x-transition class="flex-1 min-w-0">
                            <p class="text-sm text-white font-medium truncate">{{ auth()->user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="submit" class="text-xs text-slate-500 hover:text-rose-400 transition-colors">Keluar</button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="transition-all duration-300 min-h-screen flex flex-col overflow-x-hidden" 
                :class="{
                    'lg:ml-60': open,
                    'lg:ml-20': !open,
                    'ml-0': true
                }">
                {{-- Top Bar --}}
                <header class="sticky top-0 z-40 h-20 flex items-center justify-between px-8 bg-white/70 dark:bg-gray-900/70 backdrop-blur-2xl border-b border-gray-200/30 dark:border-gray-800/30 transition-all duration-300">
                    <div class="flex items-center gap-6">
                        {{-- Mobile Toggle Button --}}
                        <button @click="mobileOpen = true" class="lg:hidden p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-gray-800 transition-all active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        
                        @if (isset($header))
                            <div class="flex flex-col">
                                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] mb-0.5 opacity-80">Sistem Internal</div>
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="hidden md:flex flex-col items-end">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">{{ now()->translatedFormat('l') }}</span>
                            <span class="text-sm font-black text-slate-700 dark:text-gray-200 tracking-tight">{{ now()->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="w-px h-8 bg-gray-200 dark:bg-gray-800 hidden md:block"></div>
                        {{-- Notification or other icons can go here --}}
                        <div class="relative group cursor-pointer p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-6 h-6 text-slate-400 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="p-4 md:p-6 lg:p-8 max-w-[1600px] w-full mx-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            // Robust SweetAlert listener for Livewire 3
            window.addEventListener('swal', event => {
                // Livewire 3 might send data in event.detail[0] or event.detail
                const data = (Array.isArray(event.detail) ? event.detail[0] : event.detail) || {};
                
                if (Object.keys(data).length === 0) return;

                if (data.showLoading) {
                    Swal.fire({
                        title: data.title || 'Mohon Tunggu...',
                        text: data.text || 'Sedang memproses data...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); },
                        background: '#0f172a',
                        color: '#f1f5f9',
                        customClass: { popup: 'rounded-3xl border border-slate-800 shadow-2xl backdrop-blur-xl' }
                    });
                    return;
                }

                Swal.fire({
                    title: data.title || 'Pemberitahuan',
                    text: data.text || '',
                    icon: data.icon || 'info',
                    timer: data.timer || 3000,
                    timerProgressBar: true,
                    toast: data.toast || false,
                    position: data.position || 'center',
                    showConfirmButton: !data.toast,
                    background: '#0f172a',
                    color: '#f1f5f9',
                    confirmButtonColor: '#10b981',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-800 shadow-2xl backdrop-blur-xl',
                        confirmButton: 'px-6 py-2 rounded-xl font-black uppercase tracking-widest text-xs'
                    }
                });
            });
        </script>
    </body>
</html>
