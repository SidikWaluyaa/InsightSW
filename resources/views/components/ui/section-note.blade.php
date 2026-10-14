@props(['title', 'tag' => null, 'tone' => 'slate'])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
        'emerald' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 dark:text-emerald-400',
    ];
@endphp

{{-- Judul kelompok kartu + catatan penjelas (slot): dari mana angkanya dan apakah mengikuti filter tanggal --}}
<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <div class="flex flex-wrap items-center gap-2">
        <h2 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">{{ $title }}</h2>
        @if($tag)
            <span class="px-2 py-0.5 rounded-md border text-[9px] font-black uppercase tracking-widest {{ $tones[$tone] ?? $tones['slate'] }}">{{ $tag }}</span>
        @endif
    </div>
    @if(trim($slot) !== '')
        <p class="text-xs text-slate-500 dark:text-gray-400 leading-relaxed max-w-4xl">{{ $slot }}</p>
    @endif
</div>
