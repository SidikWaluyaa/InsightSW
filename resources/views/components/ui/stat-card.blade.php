@props([
    'label',
    'value',                // sudah diformat
    'unit' => null,
    'note' => null,         // penjelasan singkat: apa yang dihitung
    'icon' => null,
    'tone' => 'slate',      // slate | blue | emerald | amber | rose
    'badge' => null,
    'badgeTone' => 'emerald',
    'info' => null,         // array info rumus (lihat config/warehouse_formulas.php); null = tanpa tombol "i"
    'infoCall' => null,     // alternatif `info`: panggilan Livewire yang mengirim event "show-formula", mis. "showFormula(10)"
    'compact' => false,     // nilai lebih kecil, untuk grid yang rapat / nilai rupiah panjang
    'warning' => null,      // catatan merah: data belum layak jadi patokan
])

@php
    $valueTones = [
        'slate' => 'text-slate-800 dark:text-white',
        'blue' => 'text-blue-500',
        'emerald' => 'text-emerald-500',
        'amber' => 'text-amber-500',
        'rose' => 'text-rose-500',
    ];
    $iconTones = [
        'slate' => 'bg-slate-50 dark:bg-gray-800 text-slate-500',
        'blue' => 'bg-blue-500/5 text-blue-500',
        'emerald' => 'bg-emerald-500/5 text-emerald-500',
        'amber' => 'bg-amber-500/5 text-amber-500',
        'rose' => 'bg-rose-500/5 text-rose-500',
    ];
    $badgeTones = [
        'emerald' => 'bg-emerald-500/10 text-emerald-500',
        'amber' => 'bg-amber-500/10 text-amber-500',
        'rose' => 'bg-rose-500/10 text-rose-500',
        'slate' => 'bg-slate-500/10 text-slate-500',
    ];
@endphp

{{-- Kartu angka seragam: label, nilai + satuan, dan catatan apa yang dihitung --}}
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm']) }}>
    <div class="flex justify-between items-start gap-4">
        <div class="min-w-0">
            <p class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest leading-tight mb-3">
                <span>{{ $label }}</span>
                @if($info || $infoCall)
                    {{-- Tombol kecil info rumus: membuka <x-ui.formula-modal>, langsung (info) atau lewat server (infoCall) --}}
                    <button type="button"
                        @if($infoCall) wire:click="{{ $infoCall }}" wire:loading.attr="disabled" wire:target="{{ $infoCall }}"
                        @else x-data x-on:click="$dispatch('show-formula', @js($info))" @endif
                        title="Info rumus" aria-label="Info rumus {{ $label }}"
                        class="shrink-0 inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-slate-300 dark:border-gray-600 text-[8px] font-black normal-case tracking-normal leading-none text-slate-400 dark:text-gray-500 hover:border-emerald-500 hover:text-emerald-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-wait">i</button>
                @endif
            </p>
            <h3 class="{{ $compact ? 'text-2xl whitespace-nowrap' : 'text-3xl' }} font-black tracking-tight {{ $valueTones[$tone] ?? $valueTones['slate'] }}">
                {{ $value }}@if($unit) <span class="text-xs text-slate-400 font-bold uppercase ml-1">{{ $unit }}</span>@endif
            </h3>
            @if($badge)
                <span class="inline-block mt-2 text-[9px] font-black tracking-widest px-2 py-0.5 rounded uppercase {{ $badgeTones[$badgeTone] ?? $badgeTones['emerald'] }}">{{ $badge }}</span>
            @endif
            @if($note)
                <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-snug mt-2">{{ $note }}</p>
            @endif
            @if($warning)
                <p class="flex items-start gap-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400 leading-snug mt-2 px-2 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20">
                    <span aria-hidden="true">⚠</span><span>{{ $warning }}</span>
                </p>
            @endif
        </div>
        @if($icon)
            <span class="p-2.5 rounded-2xl inline-flex shrink-0 {{ $iconTones[$tone] ?? $iconTones['slate'] }}"><x-ui.icon :name="$icon" /></span>
        @endif
    </div>
</div>
