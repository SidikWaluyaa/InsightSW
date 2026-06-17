<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\WarehouseApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Piutang After')]
class FinancePiutangAfter extends Component
{
    public string $searchTerm = '';
    public string $selectedStatus = '';

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
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
    }

    public function loadData()
    {
        $this->isLoaded = true;
    }

    public function refreshData()
    {
        $cacheKey = "finance_piutang_after_data";
        Cache::forget($cacheKey);
        
        $this->page = 1;

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data piutang after terbaru dari API.',
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

    public function setPage(int $page)
    {
        $this->page = $page;
    }

    protected function getPiutangData(): array
    {
        try {
            $cacheKey = "finance_piutang_after_data";

            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $apiService = new WarehouseApiService();
                return $apiService->fetchPiutangAfterData(true);
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Finance PiutangAfter Component Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getWhatsAppLink(array $item): string
    {
        $phone = $item['customer']['phone'] ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) {
            return '#';
        }
        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $customerName = $item['customer']['name'] ?? 'Pelanggan';
        $invoiceNumber = $item['invoice_number'] ?? '';
        
        $spks = [];
        if (!empty($item['work_orders'])) {
            foreach ($item['work_orders'] as $wo) {
                if (!empty($wo['spk_number'])) {
                    $spks[] = $wo['spk_number'];
                }
            }
        }
        $spkList = implode(', ', $spks);
        
        $remainingBalance = number_format($item['financials']['remaining_balance'] ?? 0, 0, ',', '.');
        
        $message = "Halo Kak {$customerName}, kami dari Shoe Workshop menginfokan untuk Invoice *{$invoiceNumber}* dengan SPK *{$spkList}*, sepatu Kakak sudah selesai dikerjakan. Saat ini masih terdapat sisa tagihan sebesar *Rp {$remainingBalance}*. Mohon untuk segera menyelesaikan pembayaran agar sepatu bisa segera dikirim/diambil. Terima kasih!";

        return "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . rawurlencode($message);
    }

    public function render()
    {
        $rawItems = [];
        $totalPiutangCount = 0;
        $totalOutstandingSum = 0;

        if ($this->isLoaded) {
            $rawItems = $this->getPiutangData();
            
            $totalPiutangCount = count($rawItems);
            $totalOutstandingSum = collect($rawItems)->sum(function ($item) {
                return $item['financials']['remaining_balance'] ?? 0;
            });
        }

        $filteredCollection = collect($rawItems);

        if ($this->searchTerm) {
            $search = strtolower($this->searchTerm);
            $filteredCollection = $filteredCollection->filter(function ($item) use ($search) {
                $customerName = strtolower($item['customer']['name'] ?? '');
                $customerPhone = strtolower($item['customer']['phone'] ?? '');
                $invoiceNumber = strtolower($item['invoice_number'] ?? '');
                
                $spksMatch = false;
                if (!empty($item['work_orders'])) {
                    foreach ($item['work_orders'] as $wo) {
                        $spkNum = strtolower($wo['spk_number'] ?? '');
                        $brand = strtolower($wo['shoe']['brand'] ?? '');
                        $type = strtolower($wo['shoe']['type'] ?? '');
                        $color = strtolower($wo['shoe']['color'] ?? '');
                        $size = strtolower($wo['shoe']['size'] ?? '');
                        
                        if (str_contains($spkNum, $search) || 
                            str_contains($brand, $search) || 
                            str_contains($type, $search) || 
                            str_contains($color, $search) || 
                            str_contains($size, $search)) {
                            $spksMatch = true;
                            break;
                        }
                    }
                }

                return str_contains($customerName, $search) ||
                       str_contains($customerPhone, $search) ||
                       str_contains($invoiceNumber, $search) ||
                       $spksMatch;
            });
        }

        if ($this->selectedStatus) {
            $filteredCollection = $filteredCollection->filter(function ($item) {
                return strtolower($item['financials']['status'] ?? '') === strtolower($this->selectedStatus);
            });
        }

        $allStatuses = collect($rawItems)->pluck('financials.status')->unique()->filter()->values()->toArray();

        $totalResults = $filteredCollection->count();
        $totalPages = max(1, ceil($totalResults / $this->perPage));
        $paginatedItems = $filteredCollection->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.finance-piutang-after', [
            'paginatedItems' => $paginatedItems,
            'totalPiutangCount' => $totalPiutangCount,
            'totalOutstandingSum' => $totalOutstandingSum,
            'allStatuses' => $allStatuses,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $this->page,
        ]);
    }
}
