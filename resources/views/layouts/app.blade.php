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

        <title>{{ config('app.name', 'Marketing Budget Control') }}</title>

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
                <div class="relative flex items-center h-16 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span x-show="open" x-transition class="text-white font-bold text-lg tracking-tight">Algoritma</span>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="relative flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    @php
                        $subItems = [
                            ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
                            ['route' => 'daily-report', 'label' => 'Laporan Harian', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'],
                            ['route' => 'weekly-report', 'label' => 'Laporan Mingguan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
                            ['route' => 'budget-transfer', 'label' => 'Transfer Saldo', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />'],
                            ['route' => 'meta-ads', 'label' => 'Meta Ads', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
                            ['route' => 'monthly-settings', 'label' => 'Pengaturan Target', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />'],
                        ];
                        
                        $isMarketingActive = request()->routeIs(['dashboard', 'daily-report', 'weekly-report', 'budget-transfer', 'meta-ads', 'monthly-settings']);
                    @endphp

                    {{-- Marketing (Collapsible Container) --}}
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
                            <button wire:click="logout" class="text-xs text-slate-500 hover:text-rose-400 transition-colors">Keluar</button>
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
                <header class="sticky top-0 z-40 h-16 flex items-center justify-between px-6 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-800/50">
                    <div class="flex items-center gap-4">
                        {{-- Mobile Toggle Button --}}
                        <button @click="mobileOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        @if (isset($header))
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white tracking-tight">{{ $header }}</h1>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="hidden md:inline text-sm text-gray-500 dark:text-gray-400 font-medium">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="p-4 md:p-6 lg:p-8 max-w-[1600px] w-full mx-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            window.addEventListener('swal', event => {
                const data = event.detail[0];
                Swal.fire({
                    title: data.title || 'Pemberitahuan',
                    text: data.text || '',
                    icon: data.icon || 'info',
                    timer: data.timer || 3000,
                    timerProgressBar: true,
                    toast: data.toast || false,
                    position: data.position || 'center',
                    showConfirmButton: !data.toast,
                    background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                    customClass: {
                        popup: 'rounded-3xl border border-gray-100 dark:border-gray-800 shadow-2xl',
                    }
                });
            });
        </script>
    </body>
</html>
