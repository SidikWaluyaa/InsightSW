@props(['call', 'label' => ''])

{{-- Tombol kecil "i" di luar stat-card (corong, panel grafik, dll.): memanggil method Livewire yang mengirim event "show-formula" --}}
<button type="button" wire:click="{{ $call }}" wire:loading.attr="disabled" wire:target="{{ $call }}"
    title="Info rumus" aria-label="Info rumus {{ $label }}"
    {{ $attributes->merge(['class' => 'shrink-0 inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-slate-300 dark:border-gray-600 text-[8px] font-black normal-case tracking-normal leading-none text-slate-400 dark:text-gray-500 hover:border-emerald-500 hover:text-emerald-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-wait']) }}>i</button>
