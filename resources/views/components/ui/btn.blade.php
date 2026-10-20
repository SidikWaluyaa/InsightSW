@props(['variant' => 'primary', 'icon' => null, 'href' => null, 'target' => null])

@php
    $base = 'inline-flex items-center justify-center gap-2 h-11 px-5 rounded-2xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm shadow-emerald-500/20',
        'secondary' => 'bg-white hover:bg-slate-50 dark:bg-gray-900 dark:hover:bg-gray-800 text-slate-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800 shadow-sm',
        'danger' => 'bg-rose-500 hover:bg-rose-600 text-white shadow-sm shadow-rose-500/20',
    ];

    $icons = [
        'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'reset' => 'M6 18L18 6M6 6l12 12',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

{{-- Tombol seragam. `target` = nama method Livewire untuk indikator loading (ikon berputar + nonaktif). --}}
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$icon] }}" /></svg>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}
        @if($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @endif>
        @if($icon)
            <svg class="w-4 h-4 shrink-0" @if($target) wire:loading.class="animate-spin" wire:target="{{ $target }}" @endif fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$icon] }}" /></svg>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
