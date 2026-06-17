<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Data Produksi')]
class WorkshopDataProduksi extends Component
{
    public string $searchTerm = '';
    public string $selectedStatus = ''; // '', 'on_track', 'overdue', 'upcoming'
    public string $selectedService = '';
    public string $selectedCategory = '';
    public string $selectedEstimation = ''; // '', 'set', 'unset'
    public string $estimationStartDate = '';
    public string $estimationEndDate = '';

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'selectedService' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
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
        $this->selectedService = request()->query('selectedService') ?? '';
        $this->selectedCategory = request()->query('selectedCategory') ?? '';
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
        Cache::forget("workshop_production_data");
        $this->page = 1;

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data produksi terbaru dari API.',
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
        $this->selectedService = '';
        $this->selectedCategory = '';
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
    public function updatingSelectedService() { $this->page = 1; }
    public function updatingSelectedCategory() { $this->page = 1; }
    public function updatingSelectedEstimation() { $this->page = 1; }
    public function updatingEstimationStartDate() { $this->page = 1; }
    public function updatingEstimationEndDate() { $this->page = 1; }
    public function setPage(int $page) { $this->page = $page; }

    protected function getProductionData(): array
    {
        try {
            return Cache::remember("workshop_production_data", $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchProductionSummary(true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Workshop DataProduksi Component Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getItemServiceCategories(array $services): array
    {
        $categories = [];
        
        foreach ($services as $service) {
            $serviceUpper = strtoupper($service);
            
            // Check Reparasi Sol
            if (str_contains($serviceUpper, 'SOL') || 
                str_contains($serviceUpper, 'SOLE') || 
                str_contains($serviceUpper, 'JAHIT') || 
                str_contains($serviceUpper, 'LEM') || 
                str_contains($serviceUpper, 'BONGPAS') || 
                str_contains($serviceUpper, 'BONGKAR') || 
                str_contains($serviceUpper, 'MIDSOLE') || 
                str_contains($serviceUpper, 'ALAS') || 
                str_contains($serviceUpper, 'BEMPER') || 
                str_contains($serviceUpper, 'HEEL CAGE') || 
                str_contains($serviceUpper, 'STABILIZER') || 
                str_contains($serviceUpper, 'TPR') || 
                str_contains($serviceUpper, 'BIRKEN') || 
                str_contains($serviceUpper, 'VIBRAM') || 
                str_contains($serviceUpper, 'YEZZY') || 
                str_contains($serviceUpper, 'HAK') ||
                str_contains($serviceUpper, 'SWAP')) {
                $categories['Reparasi Sol'] = true;
            }
            // Check Reparasi Upper
            elseif (str_contains($serviceUpper, 'UPPER') || 
                str_contains($serviceUpper, 'LINING') || 
                str_contains($serviceUpper, 'PADDED') || 
                str_contains($serviceUpper, 'HEEL TAB') || 
                str_contains($serviceUpper, 'LIDAH') || 
                str_contains($serviceUpper, 'STRIPE') || 
                str_contains($serviceUpper, 'ZIPPER') || 
                str_contains($serviceUpper, 'CUFF') || 
                str_contains($serviceUpper, 'LACEGUARD') || 
                str_contains($serviceUpper, 'COLLAR') || 
                str_contains($serviceUpper, 'ELASTIS') || 
                str_contains($serviceUpper, 'AKSESORIS') || 
                str_contains($serviceUpper, 'CUSTOM') || 
                str_contains($serviceUpper, 'PATCH') ||
                str_contains($serviceUpper, 'LAPIS') || 
                str_contains($serviceUpper, 'BUSA')) {
                $categories['Reparasi Upper'] = true;
            }
            // Check Repaint / Treatment
            elseif (str_contains($serviceUpper, 'REPAINT') || 
                str_contains($serviceUpper, 'UNYELLOWING') || 
                str_contains($serviceUpper, 'CLEANING') || 
                str_contains($serviceUpper, 'TREATMENT') || 
                str_contains($serviceUpper, 'COLOR') || 
                str_contains($serviceUpper, 'WARNA') || 
                str_contains($serviceUpper, 'DTF') || 
                str_contains($serviceUpper, 'LASER') || 
                str_contains($serviceUpper, 'DYE')) {
                $categories['Repaint'] = true;
            }
            else {
                $categories['Lainnya'] = true;
            }
        }
        
        return array_keys($categories);
    }

    public function render()
    {
        $summary = [];
        $rawItems = [];

        if ($this->isLoaded) {
            $data = $this->getProductionData();
            $summary = $data['summary'] ?? [];
            $rawItems = $data['items'] ?? [];
        }

        // Collect unique services for dropdown filter
        $allServices = [];
        foreach ($rawItems as $item) {
            if (!empty($item['services'])) {
                foreach ($item['services'] as $svc) {
                    $allServices[$svc] = true;
                }
            }
        }
        $allServices = array_keys($allServices);
        sort($allServices);

        $allCategories = ['Reparasi Upper', 'Reparasi Sol', 'Repaint', 'Lainnya'];

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

        if ($this->selectedService) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                return in_array($this->selectedService, $item['services'] ?? []);
            });
        }

        if ($this->selectedCategory) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                $itemCategories = $this->getItemServiceCategories($item['services'] ?? []);
                if ($this->selectedCategory === 'Lainnya' && empty($item['services'])) {
                    return true;
                }
                return in_array($this->selectedCategory, $itemCategories);
            });
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
        $totalDiProduksi = $filteredCollection->count();
        $totalOverdue = $filteredCollection->filter(fn($item) => $item['is_overdue'] ?? false)->count();
        $totalUpcoming = $filteredCollection->filter(fn($item) => $item['is_upcoming'] ?? false)->count();

        $summary = [
            'total_items_in_production' => $totalDiProduksi,
            'overdue_items_count' => $totalOverdue,
            'upcoming_items_count' => $totalUpcoming,
        ];

        // Pagination
        $totalResults = $filteredCollection->count();
        $totalPages = max(1, ceil($totalResults / $this->perPage));
        $paginatedItems = $filteredCollection->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.workshop-data-produksi', [
            'summary' => $summary,
            'paginatedItems' => $paginatedItems,
            'allServices' => $allServices,
            'allCategories' => $allCategories,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
        ]);
    }
}
