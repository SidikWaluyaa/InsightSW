<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SleekflowContact;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use App\Services\SleekflowService;

#[Layout('layouts.app')]
class CsDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $lastSynced;

    protected $queryString = ['startDate', 'endDate'];

    public function mount()
    {
        // Default to today
        $this->startDate = $this->startDate ?: Carbon::now()->format('Y-m-d');
        $this->endDate = $this->endDate ?: Carbon::now()->format('Y-m-d');
        $this->lastSynced = Carbon::now();
    }

    public function autoSync(SleekflowService $service)
    {
        // For dashboard auto-sync, we just pull newest incremental data
        $service->syncContacts();
        $this->lastSynced = Carbon::now();
    }

    #[On('set-date-filters')]
    public function setDateFilters($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
    }

    public function render(SleekflowService $service)
    {
        // Get analytics data from Service Layer
        $analytics = $service->getAnalyticsData($this->startDate, $this->endDate);

        return view('livewire.cs-dashboard', array_merge($analytics, [
            'lastSynced' => $this->lastSynced,
        ]));
    }
}
