<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Data QC')]
class WorkshopDataQc extends Component
{
    public string $searchTerm = '';
    public string $selectedStatus = ''; // '', 'on_track', 'overdue', 'upcoming'
    public string $selectedEstimation = ''; // '', 'set', 'unset'
    public string $estimationStartDate = '';
    public string $estimationEndDate = '';

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'selectedEstimation' => ['except' => ''],
        'estimationStartDate' => ['except' => ''],
        'estimationEndDate' => ['except' => ''],
    ];

    public bool $isLoaded = false;
    public ?string $errorMessage = null;

    protected int $cacheTtl = 10;

    public int $perPage = 25;
    public int $page = 1;

    public function mount()
    {
        $this->searchTerm = request()->query('searchTerm') ?? '';
        $this->selectedStatus = request()->query('selectedStatus') ?? '';
        $this->selectedEstimation = request()->query('selectedEstimation') ?? '';
        $this->estimationStartDate = request()->query('estimationStartDate') ?? '';
        $this->estimationEndDate = request()->query('estimationEndDate') ?? '';
    }

    public function loadData()
    {
        $this->isLoaded = true;
    }

    public function refreshData()
    {
        Cache::forget("workshop_qc_data");
        $this->page = 1;

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data QC terbaru dari API.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->selectedStatus = '';
        $this->selectedEstimation = '';
        $this->estimationStartDate = '';
        $this->estimationEndDate = '';
        $this->page = 1;
    }

    public function setStatusFilter(string $status)
    {
        $this->selectedStatus = $status;
        $this->page = 1;
    }

    public function updatingSearchTerm() { $this->page = 1; }
    public function updatingSelectedEstimation() { $this->page = 1; }
    public function updatingEstimationStartDate() { $this->page = 1; }
    public function updatingEstimationEndDate() { $this->page = 1; }
    public function setPage(int $page) { $this->page = $page; }

    protected function getQcData(): array
    {
        try {
            return Cache::remember("workshop_qc_data", $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchQcSummary(true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Workshop DataQc Component Error: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        $summary = [];
        $rawItems = [];

        if ($this->isLoaded) {
            $data = $this->getQcData();
            $summary = $data['summary'] ?? [];
            $rawItems = $data['items'] ?? [];
        }

        // Apply filters
        $filteredCollection = collect($rawItems);

        if ($this->searchTerm) {
            $search = strtolower($this->searchTerm);
            $filteredCollection = $filteredCollection->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['spk_number'] ?? ''), $search) ||
                       str_contains(strtolower($item['customer_name'] ?? ''), $search) ||
                       str_contains(strtolower($item['shoe_brand'] ?? ''), $search) ||
                       str_contains(strtolower($item['shoe_type'] ?? ''), $search);
            });
        }

        if ($this->selectedStatus === 'on_track') {
            $filteredCollection = $filteredCollection->filter(fn($item) => !($item['is_overdue'] ?? false) && !($item['is_upcoming'] ?? false));
        } elseif ($this->selectedStatus === 'overdue') {
            $filteredCollection = $filteredCollection->filter(fn($item) => $item['is_overdue'] ?? false);
        } elseif ($this->selectedStatus === 'upcoming') {
            $filteredCollection = $filteredCollection->filter(fn($item) => $item['is_upcoming'] ?? false);
        }

        if ($this->selectedEstimation === 'set') {
            $filteredCollection = $filteredCollection->filter(fn($item) => $item['has_estimation'] ?? false);
        } elseif ($this->selectedEstimation === 'unset') {
            $filteredCollection = $filteredCollection->filter(fn($item) => !($item['has_estimation'] ?? false));
        }

        if ($this->estimationStartDate && $this->estimationEndDate) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                if (empty($item['estimation_date'])) {
                    return false;
                }
                $estDate = substr($item['estimation_date'], 0, 10);
                return $estDate >= $this->estimationStartDate && $estDate <= $this->estimationEndDate;
            });
        }

        // Calculate dynamic summary based on current filtered dataset
        $totalDiQc = $filteredCollection->count();
        $totalOverdue = $filteredCollection->filter(fn($item) => $item['is_overdue'] ?? false)->count();
        $totalUpcoming = $filteredCollection->filter(fn($item) => $item['is_upcoming'] ?? false)->count();

        $summary = [
            'total_items_in_qc' => $totalDiQc,
            'overdue_items_count' => $totalOverdue,
            'upcoming_items_count' => $totalUpcoming,
        ];

        // Pagination
        $totalResults = $filteredCollection->count();
        $totalPages = max(1, ceil($totalResults / $this->perPage));
        $paginatedItems = $filteredCollection->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.workshop-data-qc', [
            'summary' => $summary,
            'paginatedItems' => $paginatedItems,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
        ]);
    }
}
