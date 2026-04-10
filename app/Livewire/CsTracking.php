<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SleekflowContact;
use App\Services\SleekflowService;
use App\Services\SyncService;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CsTracking extends Component
{
    public $startDate;
    public $endDate;
    public $stats = [];
    public $isLoading = false;
    public $lastSyncTimestamp;
    public $isSyncing = false;
    public $directClosingDetails = [];
    public $fuClosingDetails = [];
    public $activeDetailTab = 'direct'; // direct, fu

    public function mount()
    {
        $this->startDate = $this->startDate ?: now()->format('Y-m-d');
        $this->endDate = $this->endDate ?: now()->format('Y-m-d');
        $this->updateSyncState();
        $this->checkSync(); // Force initial check
        $this->loadStats();
    }

    public function updateSyncState()
    {
        $this->lastSyncTimestamp = app(SyncService::class)->getLastSyncTime('cs_tracking_sync');
    }

    public function checkSync()
    {
        // Auto sync only for today
        if ($this->startDate !== now()->format('Y-m-d')) {
            return;
        }

        $this->isSyncing = true;
        app(SyncService::class)->syncIfAllowed('cs_tracking_sync', function() {
            // Re-sync sleekflow contacts for today
            app(SleekflowService::class)->syncContacts(now()->format('Y-m-d'), now()->format('Y-m-d'));
        }, 60);

        $this->updateSyncState();
        $this->loadStats();
        $this->isSyncing = false;
    }

    public function setDetailTab($tab)
    {
        $this->activeDetailTab = $tab;
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
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? AND DATEDIFF(closing_at, created_at_sleekflow) <= 3 THEN 1 END) as total_closing_direct,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? AND DATEDIFF(closing_at, created_at_sleekflow) > 3 THEN 1 END) as total_closing_fu,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? THEN 1 END) as total_closing_all
            ", [
                $start, $end, // greeting
                $start, $end, // konsul
                $start, $end, // followed_up
                $start, $end, // closing direct
                $start, $end, // closing fu
                $start, $end  // total closing
            ])
            ->groupBy('contact_owner_name')
            ->orderByRaw('total_closing_all DESC')
            ->get()
            ->toArray();

        // Load detailed Direct Closing contacts (<= 3 days)
        $this->directClosingDetails = SleekflowContact::query()
            ->whereBetween('closing_at', [$start, $end])
            ->whereRaw('DATEDIFF(closing_at, created_at_sleekflow) <= 3')
            ->selectRaw('first_name, last_name, phone_number, contact_owner_name, closing_at, created_at_sleekflow, DATEDIFF(closing_at, created_at_sleekflow) as chat_duration')
            ->orderBy('closing_at', 'desc')
            ->get()
            ->toArray();

        // Load detailed Follow-up Closing contacts (> 3 days)
        $this->fuClosingDetails = SleekflowContact::query()
            ->whereBetween('closing_at', [$start, $end])
            ->whereRaw('DATEDIFF(closing_at, created_at_sleekflow) > 3')
            ->selectRaw('first_name, last_name, phone_number, contact_owner_name, closing_at, created_at_sleekflow, DATEDIFF(closing_at, created_at_sleekflow) as chat_duration')
            ->orderBy('closing_at', 'desc')
            ->get()
            ->toArray();

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.cs-tracking');
    }
}
