<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WorkshopCxApiService;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CxKonfirmasiApi extends Component
{
    public $items = [];
    public $isLoading = false;
    public $searchTerm = '';
    
    // Filters (Required by API)
    public $startDate;
    public $endDate;

    public $filters = [
        'pic_name' => '',
        'response' => '',
        'spk_number' => '',
    ];
    
    // Dynamic Dropdown Options
    public $availableOptions = [
        'pic_name' => [],
        'response' => [],
        'spk_number' => [],
    ];
    
    public $perPage = 15;
    public $page = 1;

    public function mount()
    {
        // Require default dates for API to avoid fetching too much data or getting errors
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->fetch();
    }

    public function fetch()
    {
        // Validation for dates
        if (empty($this->startDate) || empty($this->endDate)) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Tanggal Kosong',
                'text'  => 'Mulai Tanggal dan Sampai Tanggal wajib diisi.',
            ]);
            return;
        }

        if ($this->startDate > $this->endDate) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Tanggal Tidak Valid',
                'text'  => 'Mulai Tanggal tidak boleh lebih besar dari Sampai Tanggal.',
            ]);
            return;
        }

        $this->isLoading = true;
        // Reset items
        $this->items = [];

        try {
            $service = new WorkshopCxApiService();
            $data = $service->fetchCxAfterConfirmation($this->startDate, $this->endDate);

            $collection = collect($data)->map(function ($item) {
                return [
                    'spk_number'     => $item['spk_number'] ?? '-',
                    'customer_name'  => $item['customer_name'] ?? '-',
                    'customer_phone' => $item['customer_phone'] ?? '-',
                    'brand_color'    => $item['brand_color'] ?? '-',
                    'entered_at'     => $item['entered_at'] ?? null,
                    'response'       => $item['response'] ?? '',
                    'pic_name'       => $item['pic_name'] ?? '-',
                    'contacted_at'   => $item['contacted_at'] ?? null,
                    'notes'          => $item['notes'] ?? '-',
                    'has_respon'     => !empty(trim($item['response'] ?? '')),
                ];
            });

            // Re-sort the date descending based on entered_at if needed, but API usually handles it.
            $this->items = $collection->values()->toArray();
            $this->extractOptions($collection);

        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'Gagal Mengambil Data API',
                'text'  => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    private function extractOptions($data)
    {
        $this->availableOptions['pic_name'] = $data->pluck('pic_name')->unique()->sort()->filter(fn($val) => $val !== '-')->values()->toArray();
        $this->availableOptions['response'] = $data->pluck('response')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['spk_number'] = $data->pluck('spk_number')->unique()->filter(fn($val) => $val !== '-')->values()->take(100)->toArray();
    }

    public function resetFilters()
    {
        $this->filters = [
            'pic_name'   => '',
            'response'   => '',
            'spk_number' => '',
        ];
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->searchTerm = '';
        $this->page = 1;
        $this->fetch();
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function render()
    {
        $col = collect($this->items);

        // Search (Nama, SPK, atau Deskripsi/Notes)
        if ($this->searchTerm) {
            $s = strtolower($this->searchTerm);
            $col = $col->filter(fn($item) =>
                str_contains(strtolower($item['customer_name'] ?? ''), $s) ||
                str_contains(strtolower($item['spk_number'] ?? ''), $s) ||
                str_contains(strtolower($item['pic_name'] ?? ''), $s) ||
                str_contains(strtolower($item['notes'] ?? ''), $s)
            );
        }

        // Dropdown filters
        foreach ($this->filters as $key => $value) {
            if ($value) {
                $col = $col->where($key, $value);
            }
        }

        // Widgets calculation
        $totalData     = $col->count();
        $totalRespon   = $col->where('has_respon', true)->count();
        $totalBelum    = $col->where('has_respon', false)->count();

        // Dynamic Status Counts (Top Categories)
        $statusCounts = $col->where('has_respon', true)
                           ->groupBy('response')
                           ->map(fn($group) => $group->count())
                           ->sortDesc();

        // Pagination
        $totalPages     = max(1, ceil($col->count() / $this->perPage));
        $paginatedItems = $col->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.cx-konfirmasi-api', [
            'paginatedItems' => $paginatedItems,
            'totalResults'   => $col->count(),
            'totalData'      => $totalData,
            'totalRespon'    => $totalRespon,
            'totalBelum'     => $totalBelum,
            'statusCounts'   => $statusCounts,
            'totalPages'     => $totalPages,
            'currentPage'    => $this->page,
        ]);
    }
}
