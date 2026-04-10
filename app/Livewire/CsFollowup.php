<?php

namespace App\Livewire;

use App\Models\SleekflowContact;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CsFollowup extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'all'; // all, urgent (>7d), warning (>3d), info (>1d)
    
    protected $queryString = [
        'search' => ['except' => ''],
        'activeTab' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Calculate counts for KPI cards
     */
    protected function getKpiCounts()
    {
        $now = now();
        
        return [
            'total' => $this->applyGapFilter(SleekflowContact::query(), 1)->count(),
            'urgent' => $this->applyGapFilter(SleekflowContact::query(), 7)->count(),
            'warning' => $this->applyGapFilter(SleekflowContact::query(), 3)->count(),
            'info' => $this->applyGapFilter(SleekflowContact::query(), 1)->count(),
        ];
    }

    /**
     * Helper to apply gap filter logic consistently
     */
    protected function applyGapFilter($query, $days)
    {
        if ($days <= 0) return $query;
        
        $threshold = now()->subDays($days);
        
        return $query->where(function($sub) use ($threshold) {
            // Case 1: Company never responded AND customer message is old enough
            $sub->where(function($inner) use ($threshold) {
                $inner->whereNull('last_contacted_from_company')
                      ->whereNotNull('last_contact_from_customers')
                      ->where('last_contact_from_customers', '<=', $threshold);
            })
            // Case 2: Customer message is newer than company response AND customer message is old enough
            ->orWhere(function($inner) use ($threshold) {
                $inner->whereNotNull('last_contacted_from_company')
                      ->whereColumn('last_contact_from_customers', '>', 'last_contacted_from_company')
                      ->where('last_contact_from_customers', '<=', $threshold);
            });
        });
    }

    public function render()
    {
        $kpis = $this->getKpiCounts();
        
        $query = SleekflowContact::query()
            ->when($this->search, function ($q) {
                $q->where(function($inner) {
                    $inner->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            });

        // Apply Tab filtering
        if ($this->activeTab === 'urgent') {
            $query = $this->applyGapFilter($query, 7);
        } elseif ($this->activeTab === 'warning') {
            $query = $this->applyGapFilter($query, 3);
        } elseif ($this->activeTab === 'info') {
            $query = $this->applyGapFilter($query, 1);
        } else {
            // Default 'all' in followup context actually means all that need followup (>1d)
            $query = $this->applyGapFilter($query, 1);
        }

        $contacts = $query->orderBy('last_contact_from_customers', 'desc')->paginate(15);

        return view('livewire.cs-followup', [
            'contacts' => $contacts,
            'kpis' => $kpis
        ]);
    }
}
