<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SleekflowContact;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CsTracking extends Component
{
    public $startDate;
    public $endDate;
    public $stats = [];
    public $isLoading = false;

    public function mount()
    {
        $this->startDate = $this->startDate ?: now()->format('Y-m-d');
        $this->endDate = $this->endDate ?: now()->format('Y-m-d');
        $this->loadStats();
    }

    public function applyFilter()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->isLoading = true;
        
        $start = $this->startDate . ' 00:00:00';
        $end = $this->endDate . ' 23:59:59';

        $this->stats = SleekflowContact::query()
            ->selectRaw("
                contact_owner_name,
                COUNT(CASE WHEN greeting_at BETWEEN ? AND ? THEN 1 END) as total_greeting,
                COUNT(CASE WHEN konsul_at BETWEEN ? AND ? THEN 1 END) as total_konsul,
                COUNT(CASE WHEN followed_up_at BETWEEN ? AND ? THEN 1 END) as total_followed_up,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? AND followed_up_at IS NULL THEN 1 END) as total_closing_direct,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? AND followed_up_at IS NOT NULL THEN 1 END) as total_closing_fu,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? THEN 1 END) as total_closing_all
            ", [
                $start, $end, // greeting
                $start, $end, // konsul
                $start, $end, // followed_up
                $start, $end, // closing direct
                $start, $end, // closing fu
                $start, $end  // total closing
            ])
            ->whereBetween('waktu_awal', [$this->startDate, $this->endDate])
            ->groupBy('contact_owner_name')
            ->orderByRaw('total_closing_all DESC')
            ->get()
            ->toArray();

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.cs-tracking');
    }
}
