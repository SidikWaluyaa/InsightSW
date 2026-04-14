<?php

namespace App\Livewire;

use App\Models\SleekflowContact;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use App\Exports\CsFollowupExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class CsFollowup extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'all'; // all, urgent (>7d), warning (>3d), info (>1d)
    public $selectedStatus = '';
    public $startDate = '';
    public $endDate = '';
    public $selectedPic = '';
    public $selectedTeam = '';
    public $selectedPriority = '';
    public $selectedSource = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'activeTab' => ['except' => 'all'],
        'selectedStatus' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'selectedPic' => ['except' => ''],
        'selectedTeam' => ['except' => ''],
        'selectedPriority' => ['except' => ''],
        'selectedSource' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
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

    public function exportExcel()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $filename = $this->getDynamicFilename('xlsx');
        $query = $this->getExportQuery();
        return Excel::download(new CsFollowupExport($query), $filename);
    }

    public function exportPdf()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        try {
            $data = $this->getExportQuery()->get();
            $filename = $this->getDynamicFilename('pdf');
            
            $pdf = Pdf::loadView('exports.cs-followup-pdf', [
                'contacts' => $data,
                'activeTab' => $this->activeTab,
                'selectedStatus' => $this->selectedStatus
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, $filename);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Export Gagal',
                'text' => 'Gagal membuat PDF karena data terlalu besar. Silakan gunakan format Excel atau kecilkan rentang filter.'
            ]);
            return null;
        }
    }

    protected function getDynamicFilename($extension)
    {
        $base = 'Followup';
        if ($this->selectedTeam) $base .= '-' . $this->selectedTeam;
        elseif ($this->selectedPic) $base .= '-' . $this->selectedPic;
        
        if ($this->activeTab !== 'all') $base .= '-' . ucfirst($this->activeTab);
        
        $base .= '-' . now()->format('dMy');
        return $base . '.' . $extension;
    }

    protected function getExportQuery()
    {
        return $this->applyAllFilters(SleekflowContact::query())
            ->orderBy('last_contact_from_customers', 'desc');
    }

    /**
     * Centralized filter logic for Consistency
     */
    protected function applyAllFilters($query)
    {
        $query->when($this->search, function ($q) {
            $q->where(function($inner) {
                $inner->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->selectedStatus, function($q) {
            $q->where('status_chat', $this->selectedStatus);
        })
        ->when($this->startDate, function($q) {
            $q->whereDate('last_contact_from_customers', '>=', $this->startDate);
        })
        ->when($this->endDate, function($q) {
            $q->whereDate('last_contact_from_customers', '<=', $this->endDate);
        })
        ->when($this->selectedPic, function($q) {
            $q->where('contact_owner_name', $this->selectedPic);
        })
        ->when($this->selectedTeam, function($q) {
            $q->where('assigned_team', $this->selectedTeam);
        })
        ->when($this->selectedPriority, function($q) {
            $q->where('priority', $this->selectedPriority);
        })
        ->when($this->selectedSource, function($q) {
            $q->where('lead_source', $this->selectedSource);
        });

        if ($this->activeTab === 'urgent') {
            $query = $this->applyGapFilter($query, 7);
        } elseif ($this->activeTab === 'warning') {
            $query = $this->applyGapFilter($query, 3);
        } elseif ($this->activeTab === 'info') {
            $query = $this->applyGapFilter($query, 1);
        } else {
            $query = $this->applyGapFilter($query, 1);
        }

        return $query;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'startDate', 'endDate', 'selectedPic', 'selectedTeam', 'selectedPriority', 'selectedSource', 'selectedStatus']);
        $this->activeTab = 'all';
        $this->resetPage();
    }

    public function render()
    {
        $kpis = $this->getKpiCounts();
        
        $query = $this->applyAllFilters(SleekflowContact::query());
        $contacts = $query->orderBy('last_contact_from_customers', 'desc')->paginate(15);
        
        $statuses = SleekflowContact::whereNotNull('status_chat')->where('status_chat', '!=', '')->distinct()->pluck('status_chat');
        $pics = SleekflowContact::whereNotNull('contact_owner_name')->where('contact_owner_name', '!=', '')->distinct()->pluck('contact_owner_name');
        $teams = SleekflowContact::whereNotNull('assigned_team')->where('assigned_team', '!=', '')->distinct()->pluck('assigned_team');
        $priorities = SleekflowContact::whereNotNull('priority')->where('priority', '!=', '')->distinct()->pluck('priority');
        $sources = SleekflowContact::whereNotNull('lead_source')->where('lead_source', '!=', '')->distinct()->pluck('lead_source');

        return view('livewire.cs-followup', [
            'contacts' => $contacts,
            'kpis' => $kpis,
            'statuses' => $statuses,
            'pics' => $pics,
            'teams' => $teams,
            'priorities' => $priorities,
            'sources' => $sources,
        ]);
    }
}
