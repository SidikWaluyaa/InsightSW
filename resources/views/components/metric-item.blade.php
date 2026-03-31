@props(['label', 'value', 'icon' => '', 'formula' => ''])

<div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 group relative">
    <div class="flex items-center gap-3 sm:gap-4">
        <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-base md:text-xl shrink-0 group-hover:scale-110 transition-transform">
            {{ $icon }}
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-1.5 mb-0.5" x-data="{ open: false }">
                <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">
                    {{ $label }}
                </p>
                @if($formula)
                <div class="relative flex items-center">
                    <button @mouseenter="open = true" @mouseleave="open = false" class="text-slate-300 dark:text-gray-600 hover:text-emerald-500 transition-colors">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                    </button>
                    <div x-show="open" x-transition.opacity
                        class="absolute top-full right-0 mt-2 w-48 p-2 bg-slate-800 dark:bg-gray-800 text-[10px] text-white rounded-lg shadow-xl z-[70] pointer-events-none text-center leading-tight">
                        <span class="font-bold text-emerald-400 uppercase">Rumus:</span><br>{{ $formula }}
                        <div class="absolute bottom-full right-2 border-8 border-transparent border-b-slate-800 dark:border-b-gray-800"></div>
                    </div>
                </div>
                @endif
            </div>
            <p class="text-base md:text-lg font-black text-slate-800 dark:text-white leading-tight">
                {{ $value }}
            </p>
        </div>
    </div>
</div>
