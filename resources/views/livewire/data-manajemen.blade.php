<div class="space-y-6">
    <x-ui.page-header title="Data Manajemen SW" subtitle="31 data Iklan & CS sesuai dokumen Tim Data, dibandingkan dengan periode yang sama bulan lalu.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate" />

    <div wire:loading.class="opacity-25 transition-opacity" wire:target="setDateRange">
        <livewire:management-metrics :start="$startDate" :end="$endDate" />
    </div>
</div>
