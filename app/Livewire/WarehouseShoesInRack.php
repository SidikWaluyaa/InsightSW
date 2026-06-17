<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Sepatu di Rak')]
class WarehouseShoesInRack extends Component
{
    public string $searchTerm = '';
    public string $selectedStatus = '';
    public bool $showDonationOnly = false;

    // Tracking active queries in URL
    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'showDonationOnly' => ['except' => false],
    ];

    public bool $isLoaded = false;
    public ?string $errorMessage = null;

    // Cache TTL in seconds (10 seconds)
    protected int $cacheTtl = 10;
    
    // Pagination parameters
    public int $perPage = 25;
    public int $page = 1;

    public function mount()
    {
        $this->searchTerm = request()->query('searchTerm') ?? '';
        $this->selectedStatus = request()->query('selectedStatus') ?? '';
        $this->showDonationOnly = request()->query('showDonationOnly') == '1';
    }

    public function loadData()
    {
        $this->isLoaded = true;
    }

    public function refreshData()
    {
        $cacheKey = "warehouse_shoerack_data";
        Cache::forget($cacheKey);
        
        $this->page = 1;

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data rak sepatu terbaru dari API.',
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
        $this->showDonationOnly = false;
        $this->page = 1;
    }

    public function updatingSearchTerm()
    {
        $this->page = 1;
    }

    public function updatingSelectedStatus()
    {
        $this->page = 1;
    }

    public function updatingShowDonationOnly()
    {
        $this->page = 1;
    }

    public function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * Fetch shoerack data from API.
     */
    protected function getShoerackData(): array
    {
        try {
            $cacheKey = "warehouse_shoerack_data";

            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchShoerackData(true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Warehouse ShoesInRack Component Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate WhatsApp Message Link.
     */
    public function getWhatsAppLink(array $item, string $type = 'notification'): string
    {
        $phone = $item['customer']['phone'] ?? '';
        
        // Clean phone number (must start with country code, without +, spaces or special chars)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) {
            return '#';
        }

        $customerName = $item['customer']['name'] ?? 'Pelanggan';
        $brand = $item['shoe']['brand'] ?? '';
        $shoeType = $item['shoe']['type'] ?? '';
        $color = $item['shoe']['color'] ?? 'Warna N/A';
        $size = $item['shoe']['size'] ?? '';
        $rackCode = $item['storage']['rack_code'] ?? '-';
        $daysStored = $item['storage']['days_stored'] ?? 0;
        
        $shoeDetail = trim("{$brand} {$shoeType}");
        if (!empty($color)) {
            $shoeDetail .= " ({$color})";
        }
        if (!empty($size)) {
            $shoeDetail .= " Size {$size}";
        }

        if ($type === 'donation') {
            $message = "Halo Kak {$customerName}, kami dari Shoe Workshop menginfokan bahwa sepatu Kakak: *{$shoeDetail}* di Rak *RAK: {$rackCode}* telah disimpan selama *{$daysStored} hari* (> 3 bulan).\n\nSesuai dengan ketentuan kami, sepatu yang disimpan lebih dari 3 bulan akan direkomendasikan masuk program donasi. Mohon segera mengonfirmasi pengambilan sepatu Kakak. Terima kasih!";
        } else {
            $message = "Halo Kak {$customerName}, kami dari Shoe Workshop menginfokan bahwa sepatu Kakak: *{$shoeDetail}* saat ini sudah selesai pengerjaan dan disimpan di Rak *RAK: {$rackCode}*. Mohon untuk segera diambil ya Kak. Terima kasih!";
        }

        return "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . rawurlencode($message);
    }

    public function render()
    {
        $rawItems = [];
        $totalShoes = 0;
        $donationCandidatesCount = 0;

        if ($this->isLoaded) {
            $rawItems = $this->getShoerackData();
            
            // Calculate header KPI counts from raw data
            $totalShoes = count($rawItems);
            $donationCandidatesCount = collect($rawItems)->filter(function ($item) {
                return ($item['storage']['is_donation_candidate'] ?? false) || ($item['storage']['days_stored'] ?? 0) >= 90;
            })->count();
        }

        // Apply filters
        $filteredCollection = collect($rawItems);

        if ($this->searchTerm) {
            $search = strtolower($this->searchTerm);
            $filteredCollection = $filteredCollection->filter(function ($item) use ($search) {
                $customerName = strtolower($item['customer']['name'] ?? '');
                $spkNumber = strtolower($item['spk_number'] ?? '');
                $rackCode = strtolower($item['storage']['rack_code'] ?? '');
                $brand = strtolower($item['shoe']['brand'] ?? '');
                $type = strtolower($item['shoe']['type'] ?? '');
                $color = strtolower($item['shoe']['color'] ?? '');
                $size = strtolower($item['shoe']['size'] ?? '');

                return str_contains($customerName, $search) ||
                       str_contains($spkNumber, $search) ||
                       str_contains($rackCode, $search) ||
                       str_contains($brand, $search) ||
                       str_contains($type, $search) ||
                       str_contains($color, $search) ||
                       str_contains($size, $search);
            });
        }

        if ($this->selectedStatus) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                return strtoupper($item['wo_status'] ?? '') === strtoupper($this->selectedStatus);
            });
        }

        if ($this->showDonationOnly) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                return ($item['storage']['is_donation_candidate'] ?? false) || ($item['storage']['days_stored'] ?? 0) >= 90;
            });
        }

        // Gather unique statuses from the filtered or raw list for dropdown filtering
        $allStatuses = collect($rawItems)->pluck('wo_status')->unique()->filter()->values()->toArray();

        // Pagination
        $totalResults = $filteredCollection->count();
        $totalPages = max(1, ceil($totalResults / $this->perPage));
        $paginatedItems = $filteredCollection->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.warehouse-shoes-in-rack', [
            'paginatedItems' => $paginatedItems,
            'totalShoes' => $totalShoes,
            'donationCandidatesCount' => $donationCandidatesCount,
            'allStatuses' => $allStatuses,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
        ]);
    }
}
