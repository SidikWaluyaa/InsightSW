@props(['start', 'end', 'updated' => null])

{{-- Baris info seragam di bawah header: periode aktif (kiri), keterangan tambahan (slot) & waktu data (kanan) --}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-gray-400']) }}>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <span>Menampilkan data <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ \Carbon\Carbon::parse($start)->translatedFormat('j M Y') }} – {{ \Carbon\Carbon::parse($end)->translatedFormat('j M Y') }}</span></span>
        {{ $slot }}
    </div>
    <div class="flex items-center gap-3">
        <span wire:loading.delay class="text-emerald-500 font-bold">Memuat…</span>
        @if($updated)
            <span>Data per <span class="font-bold font-mono text-slate-700 dark:text-gray-200">{{ $updated }}</span></span>
        @endif
    </div>
</div>
