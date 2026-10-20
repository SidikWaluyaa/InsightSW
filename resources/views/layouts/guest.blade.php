<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
    :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'Shoe Workshop') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800 dark:text-gray-100 bg-gray-50 dark:bg-gray-950">
        <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">

            {{-- Panel brand (hanya layar besar) --}}
            <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 xl:p-16 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -top-32 -left-32 w-[28rem] h-[28rem] rounded-full bg-emerald-500/15 blur-3xl"></div>
                    <div class="absolute -bottom-40 -right-24 w-[30rem] h-[30rem] rounded-full bg-teal-400/10 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 28px 28px;"></div>
                </div>

                <a href="/" class="relative flex items-center gap-4">
                    <img src="{{ asset('assets/logo-icon.png') }}" alt="Shoe Workshop" class="w-14 h-14 object-contain drop-shadow-[0_0_10px_rgba(52,211,153,0.35)]">
                    <span class="flex flex-col">
                        <span class="text-white font-black text-xs uppercase tracking-[0.4em] leading-tight">Shoe</span>
                        <span class="text-emerald-400 font-black text-xs uppercase tracking-[0.4em] leading-tight">Workshop</span>
                    </span>
                </a>

                <div class="relative max-w-md">
                    <p class="text-[11px] font-black uppercase tracking-[0.3em] text-emerald-400 mb-4">Sistem Internal</p>
                    <h2 class="text-4xl xl:text-5xl font-black tracking-tight leading-[1.1]">
                        Satu dasbor untuk seluruh operasional.
                    </h2>
                    <p class="mt-5 text-sm leading-relaxed text-slate-300">
                        Pantau iklan, percakapan pelanggan, keuangan, gudang, dan produksi workshop dari satu tempat.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach (['Marketing', 'Customer Service', 'CX & QC', 'Finance', 'Gudang', 'Workshop'] as $division)
                            <span class="px-3 py-1.5 rounded-full border border-white/10 bg-white/5 text-[11px] font-semibold text-slate-200">{{ $division }}</span>
                        @endforeach
                    </div>
                </div>

                <p class="relative text-[11px] text-slate-400">
                    &copy; {{ now()->year }} Shoe Workshop. Hanya untuk penggunaan internal.
                </p>
            </aside>

            {{-- Panel form --}}
            <main class="relative flex min-h-screen flex-col items-center justify-center px-5 py-10 sm:px-8">
                {{-- Tombol mode gelap/terang --}}
                <button type="button" @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="absolute top-5 right-5 p-2.5 rounded-xl text-slate-500 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    :aria-label="darkMode ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>

                <div class="w-full max-w-[420px]">
                    {{-- Logo untuk layar kecil (panel brand disembunyikan) --}}
                    <a href="/" class="lg:hidden flex items-center justify-center gap-3 mb-8">
                        <img src="{{ asset('assets/logo-icon.png') }}" alt="Shoe Workshop" class="w-12 h-12 object-contain">
                        <span class="flex flex-col">
                            <span class="text-slate-800 dark:text-white font-black text-[11px] uppercase tracking-[0.4em] leading-tight">Shoe</span>
                            <span class="text-emerald-500 font-black text-[11px] uppercase tracking-[0.4em] leading-tight">Workshop</span>
                        </span>
                    </a>

                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-none p-7 sm:p-9">
                        {{ $slot }}
                    </div>

                    <p class="lg:hidden mt-6 text-center text-[11px] text-slate-400 dark:text-gray-500">
                        &copy; {{ now()->year }} Shoe Workshop. Hanya untuk penggunaan internal.
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
