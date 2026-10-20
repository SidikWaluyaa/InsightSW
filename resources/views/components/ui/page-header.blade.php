@props(['title', 'subtitle' => null, 'badge' => null, 'eyebrow' => 'Sistem Internal'])

{{-- Header halaman seragam: judul di kiri, filter & tombol aksi (slot) di kanan --}}
<div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row lg:items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <div class="text-[10px] font-bold tracking-[0.2em] text-emerald-500 uppercase mb-1">{{ $eyebrow }}</div>
        <h1 class="flex flex-wrap items-center gap-x-3 gap-y-1 text-2xl md:text-3xl font-black text-slate-800 dark:text-white tracking-tight">
            <span>{{ $title }}</span>
            @if($badge)
                <span class="text-[10px] px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20 uppercase font-black tracking-widest">{{ $badge }}</span>
            @endif
        </h1>
        @if($subtitle)
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    @if(trim($slot) !== '')
        <div class="flex flex-col items-stretch lg:items-end gap-2 w-full lg:w-auto shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>
