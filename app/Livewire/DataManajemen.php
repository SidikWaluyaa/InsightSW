<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman Data Manajemen SW: 31 data Iklan & CS (Presentasi Tim Data + Kamus Metrik v2.0) dengan lampu MoM.
 * Dipindah dari Dashboard supaya Dashboard mengikuti PRD (4 kartu utama).
 */
#[Layout('layouts.app')]
class DataManajemen extends Component
{
    use WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    public function mount(): void
    {
        $max = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $max->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $max->toDateString()
        );
    }

    /** Sleekflow belum menyediakan data hari berjalan, jadi rentang paling akhir kemarin (H-1). */
    protected function maxSelectableDate(): Carbon
    {
        return now()->subDay()->startOfDay();
    }

    public function render()
    {
        return view('livewire.data-manajemen', [
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
