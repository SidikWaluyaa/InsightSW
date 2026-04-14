<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GoogleSheetService;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CxKonfirmasiAfter extends Component
{
    public $spreadsheetUrl = 'https://docs.google.com/spreadsheets/d/1KEvlxH5C-KgAlAL7ahKaIXlWCRapkqTl0-GYCCNlqTg/edit';
    public $gid = '1022999064';

    public $items = [];
    public $isLoading = false;
    public $searchTerm = '';
    
    // Filters
    public $startDate = '';
    public $endDate = '';
    public $filters = [
        'pic'             => '',
        'respon_customer' => '',
        'tahap_lanjutan'  => '',
        'no_spk'          => '',
    ];
    
    // Dynamic Dropdown Options
    public $availableOptions = [
        'pic'             => [],
        'respon_customer' => [],
        'tahap_lanjutan'  => [],
        'no_spk'          => [],
    ];
    
    public $perPage = 15;
    public $page = 1;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->fetch();
    }

    public function fetch()
    {
        $this->isLoading = true;
        $service = new GoogleSheetService();

        try {
            // Headers are on Row 5, so skip 4 rows
            $allData = $service->fetchData($this->spreadsheetUrl, $this->gid, 4);

            if ($allData->isEmpty()) {
                throw new \Exception("Data Google Sheet kosong atau tidak dapat diakses.");
            }

            $filtered = $allData->map(function ($item) {
                $carbonDate = $this->parseDate($item['tanggal'] ?? '');
                return [
                    'tanggal'         => $item['tanggal'] ?? '-',
                    'nama_customer'   => $item['nama_customer'] ?? '-',
                    'no_spk'          => $item['no_spk'] ?? '-',
                    'pic'             => $item['pic'] ?? '-',
                    'kontak_customer' => $item['kontak_customer'] ?? '-',
                    'respon_customer' => $item['respon_customer'] ?? '-',
                    'tahap_lanjutan'  => $item['tahap_lanjutan'] ?? '-',
                    'catatan_gudang'  => $item['catatan_gudang'] ?? '-',
                    'keterangan'      => $item['keterangan'] ?? '-',
                    'carbon_date'     => $carbonDate ? $carbonDate->format('Y-m-d') : null,
                    'has_respon'      => !empty(trim($item['respon_customer'] ?? '')),
                ];
            })->filter(function ($item) {
                if (!$item['carbon_date']) return false;
                // Minimum data is Jan 2026 as per base requirement
                return $item['carbon_date'] >= '2026-01-01';
            });

            $this->items = $filtered->values()->toArray();
            $this->extractOptions($filtered);

        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'Gagal Mengambil Data',
                'text'  => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    private function parseDate(string $raw)
    {
        if (empty($raw) || $raw === '-') return null;

        // Handle Indonesian month names for Carbon compatibility
        $indonesianMonths = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'Mei' => 'May', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Agu' => 'Aug', 'Sep' => 'Sep', 'Okt' => 'Oct', 'Nov' => 'Nov', 'Des' => 'Dec',
            'Agustus' => 'August', 'Desember' => 'December', 'Oktober' => 'October'
        ];

        $cleaned = $raw;
        foreach ($indonesianMonths as $id => $en) {
            $cleaned = str_ireplace($id, $en, $cleaned);
        }

        try {
            return \Illuminate\Support\Carbon::parse($cleaned);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractOptions($data)
    {
        $this->availableOptions['pic'] = $data->pluck('pic')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['respon_customer'] = $data->pluck('respon_customer')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['tahap_lanjutan'] = $data->pluck('tahap_lanjutan')->unique()->sort()->filter()->values()->toArray();
        
        // No SPK might be too many for a select if it's every row, but let's try if it's categorized
        // Usually it's better to keep SPK as a search, but user asked for "flexible"
        $this->availableOptions['no_spk'] = $data->pluck('no_spk')->unique()->sort()->filter()->values()->take(100)->toArray();
    }

    public function resetFilters()
    {
        $this->filters = [
            'pic'             => '',
            'respon_customer' => '',
            'tahap_lanjutan'  => '',
            'no_spk'          => '',
        ];
        $this->startDate = '';
        $this->endDate = '';
        $this->searchTerm = '';
        $this->page = 1;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function render()
    {
        $col = collect($this->items);

        // Search (Nama, SPK, atau Keterangan)
        if ($this->searchTerm) {
            $s = strtolower($this->searchTerm);
            $col = $col->filter(fn($item) =>
                str_contains(strtolower($item['nama_customer'] ?? ''), $s) ||
                str_contains(strtolower($item['no_spk'] ?? ''), $s) ||
                str_contains(strtolower($item['pic'] ?? ''), $s) ||
                str_contains(strtolower($item['catatan_gudang'] ?? ''), $s)
            );
        }

        // Date Range
        if ($this->startDate) {
            $col = $col->where('carbon_date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $col = $col->where('carbon_date', '<=', $this->endDate);
        }

        // Dropdown filters
        foreach ($this->filters as $key => $value) {
            if ($value) {
                $col = $col->where($key, $value);
            }
        }

        // Widgets (Reflecting Filtered State)
        $totalData     = $col->count();
        $totalRespon   = $col->where('has_respon', true)->count();
        $totalBelum    = $col->where('has_respon', false)->count();

        // Dynamic Status Counts (Top Categories)
        $statusCounts = $col->where('has_respon', true)
                           ->groupBy('respon_customer')
                           ->map(fn($group) => $group->count())
                           ->sortDesc();

        // Pagination
        $totalPages     = max(1, ceil($col->count() / $this->perPage));
        $paginatedItems = $col->slice(($this->page - 1) * $this->perPage, $this->perPage)->values()->all();

        return view('livewire.cx-konfirmasi-after', [
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
