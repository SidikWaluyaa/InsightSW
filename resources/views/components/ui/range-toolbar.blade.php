@props([
    'start',
    'end',
    'max' => null,              // Y-m-d, default hari ini
    'presets' => [],            // dari $this->datePresets()
    'refresh' => null,          // nama method Livewire untuk tombol "Perbarui Data"
    'refreshLabel' => 'Perbarui Data',
])

@php $max = $max ?: now()->toDateString(); @endphp

{{--
    Toolbar filter seragam: date range picker + tombol perbarui + preset cepat.
    Komponen Livewire pemakai harus memakai trait App\Livewire\Concerns\WithDateRange.
--}}
@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <style>
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange,
        .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover { background: #10b981; border-color: #10b981; color: #fff; }
        .flatpickr-day.inRange { background: rgba(16, 185, 129, .15); border-color: transparent; box-shadow: -5px 0 0 rgba(16, 185, 129, .15), 5px 0 0 rgba(16, 185, 129, .15); }

        /* Mode gelap */
        .dark .flatpickr-calendar { background: #1e293b; border: 1px solid #334155; box-shadow: 0 20px 40px rgba(0, 0, 0, .5); }
        .dark .flatpickr-calendar.arrowTop:before, .dark .flatpickr-calendar.arrowTop:after { border-bottom-color: #1e293b; }
        .dark .flatpickr-calendar.arrowBottom:before, .dark .flatpickr-calendar.arrowBottom:after { border-top-color: #1e293b; }
        .dark .flatpickr-months .flatpickr-month, .dark .flatpickr-current-month, .dark .flatpickr-current-month input.cur-year { color: #f8fafc; fill: #f8fafc; }
        .dark .flatpickr-months .flatpickr-prev-month, .dark .flatpickr-months .flatpickr-next-month { color: #f8fafc; fill: #f8fafc; }
        .dark .flatpickr-current-month .flatpickr-monthDropdown-months { background: #1e293b; color: #f8fafc; }
        .dark span.flatpickr-weekday { color: #94a3b8; }
        .dark .flatpickr-day { color: #cbd5e1; }
        .dark .flatpickr-day:hover { background: #334155; border-color: #334155; }
        .dark .flatpickr-day.flatpickr-disabled, .dark .flatpickr-day.prevMonthDay, .dark .flatpickr-day.nextMonthDay { color: #475569; }
        .dark .flatpickr-day.inRange { color: #6ee7b7; }
        .dark .flatpickr-day.selected, .dark .flatpickr-day.startRange, .dark .flatpickr-day.endRange { color: #fff; }
    </style>
@endonce

<div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
    {{-- Date range picker --}}
    <div wire:ignore
        class="flex items-center gap-2 h-11 px-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm"
        x-data="{
            picker: null,
            fmt(d) { return this.picker.formatDate(d, 'Y-m-d'); },
            init() {
                this.picker = flatpickr(this.$refs.range, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'j M Y',
                    maxDate: @js($max),
                    locale: (flatpickr.l10ns && flatpickr.l10ns.id) || 'default',
                    defaultDate: [@js($start), @js($end)],
                    onClose: (dates) => {
                        // Klik 1 tanggal saja = rentang 1 hari
                        if (dates.length === 1) dates = [dates[0], dates[0]];
                        if (dates.length === 2) $wire.setDateRange(this.fmt(dates[0]), this.fmt(dates[1]));
                    },
                });
            },
        }"
        x-on:date-range-updated.window="picker.setDate([$event.detail.start, $event.detail.end], false)">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <input x-ref="range" type="text" readonly placeholder="Pilih rentang tanggal" aria-label="Rentang tanggal"
            class="bg-transparent border-none p-0 w-full sm:w-[190px] text-xs font-bold text-slate-700 dark:text-gray-200 placeholder-slate-400 cursor-pointer focus:ring-0 focus:outline-none">
    </div>

    {{ $slot }}

    @if($refresh)
        <x-ui.btn icon="refresh" :target="$refresh" wire:click="{{ $refresh }}">{{ $refreshLabel }}</x-ui.btn>
    @endif
</div>

@if(!empty($presets))
    <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2">
        @foreach($presets as $preset)
            <button type="button" wire:click="setDateRange('{{ $preset['start'] }}', '{{ $preset['end'] }}')"
                class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border transition-all {{ $start === $preset['start'] && $end === $preset['end'] ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-white dark:bg-gray-900 text-slate-500 dark:text-gray-400 border-gray-200 dark:border-gray-800 hover:text-slate-800 dark:hover:text-white' }}">
                {{ $preset['label'] }}
            </button>
        @endforeach
    </div>
@endif
