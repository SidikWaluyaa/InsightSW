<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDateRange;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Dashboard Eksekutif (PRD halaman 1, Executive Overview): 4 kartu utama, corong chat sampai sepatu masuk gudang,
 * tren omset vs biaya iklan, dan status sepatu masuk. Isi dihitung di komponen ExecutiveOverview.
 */
#[Layout('layouts.app')]
class ExecutiveDashboard extends Component
{
    use WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    public function mount(): void
    {
        $today = $this->maxSelectableDate();
        [$this->startDate, $this->endDate] = $this->normalizeDateRange(
            $this->startDate ?: $today->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $today->toDateString()
        );
    }

    public function render()
    {
        return view('livewire.executive-dashboard', [
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }
}
