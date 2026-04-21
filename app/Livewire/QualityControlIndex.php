<?php

namespace App\Livewire;

use App\Services\GoogleSheetService;
use App\Models\QualityControlSnapshot;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class QualityControlIndex extends Component
{
    public $spreadsheetUrl = '';
    public $gid = '';
    public $items = [];
    public $isLoading = false;
    public $errorMessage = '';
    
    // Pagination & Search
    public $searchTerm = '';
    public $perPage = 30;
    public $page = 1;

    // Dynamic Filters
    public $filters = [
        'jenis_barang' => '',
        'status'       => '',
        'step'         => '',
        'checklist'    => '',
    ];

    public $startDate = '';
    public $endDate = '';
    public $widgetDate = '';
    public $baselineCount = 0; // Snapshot for "Morning Data"

    public $availableOptions = [
        'jenis_barang' => [],
        'status'       => [],
        'step'         => [],
        'checklist'    => [],
    ];

    protected $rules = [
        'spreadsheetUrl' => 'required|url',
        'gid' => 'required|numeric',
    ];

    public function mount(GoogleSheetService $service)
    {
        // Pre-fill with user's example if empty (optional but helpful for first time)
        $this->spreadsheetUrl = $this->spreadsheetUrl ?: 'https://docs.google.com/spreadsheets/d/1Gok4uNalu5P5pRXrCWqcPwtOmodBuAZwI_L0OHH2gS8/edit';
        $this->gid = $this->gid ?: '1019775130';
        $this->widgetDate = date('Y-m-d'); 

        // Auto-fetch on initial load
        if ($this->spreadsheetUrl && $this->gid) {
            $this->fetch($service);
        }
    }

    /**
     * Reset page when searching or filtering.
     */
    public function updatedSearchTerm() { $this->page = 1; }
    public function updatedFilters() { $this->page = 1; }
    public function updatedStartDate() { $this->page = 1; }
    public function updatedEndDate() { $this->page = 1; }
    public function updatedWidgetDate() { $this->page = 1; }

    /**
     * Fetch data from Google Sheets with 60s Caching.
     */
    public function fetch(GoogleSheetService $service, bool $force = false)
    {
        $this->validate();
        $this->isLoading = true;
        $this->errorMessage = '';
        $this->items = [];
        $this->page = 1;

        if ($force) {
            $this->resetFilters();
        }

        try {
            $spreadsheetId = $this->extractId($this->spreadsheetUrl);
            $cacheKey = "qc_data_{$spreadsheetId}_{$this->gid}";

            // If forced, clear existing cache
            if ($force) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            // Global Cache for 60 seconds to prevent redundant API calls on refresh
            $cachedItems = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($service) {
                $data = $service->fetchData($this->spreadsheetUrl, $this->gid);
                
                if ($data->isEmpty()) return [];

                return $data->filter(function($item) {
                    return !empty($item['id']) && !empty($item['spk_number']);
                })->map(function($item) {
                    $rawDate = $item['tanggal_kirim'] ?? ''; // Normalized key from 'tanggal kirim' or 'Tgl Kirim'
                    $carbonDate = $this->parseSheetDate($rawDate);

                    return [
                        'id'             => $item['id'] ?? '-',
                        'spk_number'     => $item['spk_number'] ?? '-',
                        'jenis_barang'   => $item['jenis_barang'] ?? '-',
                        'customer_name'  => $item['customer_name'] ?? '-',
                        'customer_phone' => $item['customer_phone'] ?? '-',
                        'status'         => $item['status'] ?? '-',
                        'step'           => $item['step'] ?? '-',
                        'link_pdf'       => $item['link_pdf'] ?? null,
                        'tanggal_kirim'  => $rawDate,
                        'carbon_date'    => $carbonDate ? $carbonDate->toDateString() : null, // Store as Y-m-d for comparison
                        'checklist'      => $this->mapChecklist($item['checklist'] ?? ''),
                        'raw_checklist'  => strtoupper(trim($item['checklist'] ?? '')),
                    ];
                })->toArray();
            });

            $this->items = $cachedItems;
            
            if (empty($this->items)) {
                $this->errorMessage = "Tidak ada data yang ditemukan di sheet tersebut.";
                return;
            }

            // Extract options for dynamic filters from the cached items (collection)
            $this->extractOptions(collect($this->items));

            // LOGIC: Maintain Today's Baseline for Shift Performance
            $this->initBaseline();

            if ($force) {
                $this->dispatch('swal', [
                    'icon'    => 'success',
                    'title'   => 'Update Data Berhasil',
                    'text'    => "Berhasil memuat " . count($this->items) . " catatan pesanan terbaru.",
                    'timer'   => 3000
                ]);
            }

        } catch (\Exception $e) {
            $this->errorMessage = "Error: " . $e->getMessage();
            $this->dispatch('swal', [
                'icon'    => 'error',
                'title'   => 'Gagal Mengambil Data',
                'text'    => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Initialize today's baseline snapshot.
     */
    private function initBaseline()
    {
        $today = date('Y-m-d');
        $snapshot = QualityControlSnapshot::where('snapshot_date', $today)->first();

        if ($snapshot) {
            $this->baselineCount = $snapshot->baseline_count;
        } else {
            // First time today - take a snapshot of current TOTAL verified (All-time)
            $allTimeVerified = collect($this->items)
                ->where('raw_checklist', 'TRUE')
                ->count();
            
            $this->baselineCount = $allTimeVerified;
            
            QualityControlSnapshot::create([
                'snapshot_date' => $today,
                'baseline_count' => $allTimeVerified,
            ]);
        }
    }

    /**
     * Manual trigger to capture current data as shift baseline.
     */
    public function takeManualSnapshot()
    {
        $today = date('Y-m-d');
        $allTimeVerified = collect($this->items)
            ->where('raw_checklist', 'TRUE')
            ->count();
        
        $this->baselineCount = $allTimeVerified;

        QualityControlSnapshot::updateOrCreate(
            ['snapshot_date' => $today],
            ['baseline_count' => $allTimeVerified]
        );

        $this->dispatch('swal', [
            'icon'    => 'success',
            'title'   => 'Patokan Harian Dicatat',
            'text'    => "Target harian (baseline) disetel ke: $allTimeVerified Pesanan.",
            'timer'   => 3000
        ]);
    }

    /**
     * Parse Google Sheets date (M/D/Y confirmed by user) into Carbon.
     */
    private function parseSheetDate(string $rawDate)
    {
        if (empty($rawDate) || $rawDate === '-') return null;
        
        try {
            // User confirmed Bulan Hari Tahun (M/D/Y)
            // Format can be 4/6/2026 or 04/06/2026
            return \Illuminate\Support\Carbon::createFromFormat('n/j/Y', $rawDate);
        } catch (\Exception $e) {
            try {
                // Try fallback format (M/D/Y with leading zeros)
                return \Illuminate\Support\Carbon::createFromFormat('m/d/Y', $rawDate);
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    /**
     * Extract ID from URL for cache key.
     */
    private function extractId($url)
    {
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? 'default';
    }

    /**
     * Extract unique options from the dataset for dynamic filters.
     */
    private function extractOptions(\Illuminate\Support\Collection $data)
    {
        $this->availableOptions['jenis_barang'] = $data->pluck('jenis_barang')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['status']       = $data->pluck('status')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['step']         = $data->pluck('step')->unique()->sort()->filter()->values()->toArray();
        $this->availableOptions['checklist']    = ['Selesai QC (Lancar)', 'Belum Dicek / Tertunda'];
    }

    /**
     * Reset all active filters.
     */
    public function resetFilters()
    {
        $this->filters = [
            'jenis_barang' => '',
            'status'       => '',
            'step'         => '',
            'checklist'    => '',
        ];
        $this->startDate = '';
        $this->endDate = '';
        $this->searchTerm = '';
        $this->page = 1;
    }

    /**
     * Set current page.
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * Specialized logic for 'checklist' column.
     */
    private function mapChecklist(string $value): string
    {
        $value = strtoupper(trim($value));
        if ($value === 'TRUE') return 'Selesai QC (Lancar)';
        return 'Belum Dicek / Tertunda';
    }

    public function render()
    {
        // Prepare base collection for calculations
        $baseCollection = collect($this->items);

        // Apply Global Search & Category Filters (Affects BOTH Table and Widgets)
        $filteredCollection = $baseCollection;

        if ($this->searchTerm) {
            $filteredCollection = $filteredCollection->filter(function($item) {
                return str_contains(strtolower($item['spk_number'] ?? ''), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['customer_name'] ?? ''), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['id'] ?? ''), strtolower($this->searchTerm));
            });
        }

        foreach ($this->filters as $column => $value) {
            if ($value) {
                $filteredCollection = $filteredCollection->filter(function($item) use ($column, $value) {
                    return $item[$column] === $value;
                });
            }
        }

        // --- WIDGET LOGIC (Optimized for Operational Control - 4 WIDGET SYSTEM) ---
        
        $baseCol = collect($this->items);

        // Filter the collection for W2 and W3 only (using category filters)
        $categoryFilteredCol = $baseCol;
        if ($this->searchTerm) {
            $categoryFilteredCol = $categoryFilteredCol->filter(function($item) {
                return str_contains(strtolower($item['id'] ?? ''), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['spk_number'] ?? ''), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['customer_name'] ?? ''), strtolower($this->searchTerm));
            });
        }
        foreach ($this->filters as $key => $value) {
            if ($value) {
                $categoryFilteredCol = $categoryFilteredCol->where($key, $value);
            }
        }

        // W1: Baseline (Fetch from DB based on selected widgetDate)
        $snapshot = QualityControlSnapshot::where('snapshot_date', $this->widgetDate)->first();
        $w1_baseline = $snapshot ? $snapshot->baseline_count : 0;
        $w1_time     = $snapshot ? $snapshot->updated_at->format('H:i') . ' WIB' : '--:--';

        // W2: Sudah Verifikasi Real Time (Follows Category Filters)
        $w2_realtime_verified = $categoryFilteredCol
            ->where('raw_checklist', 'TRUE')
            ->count();
        
        // W3: Belum Verifikasi Real Time (Follows Category Filters)
        $w3_total_backlog = $categoryFilteredCol
            ->where('raw_checklist', '!=', 'TRUE')
            ->count();

        // W4: Widget Verifikasi Rumus (W2 TOTAL - W1 TOTAL) 
        // Note: W4 uses Global Totals to maintain formula integrity
        $globalW2 = $baseCol->where('raw_checklist', 'TRUE')->count();
        $w4_shift_achievement = $globalW2 - $w1_baseline;

        // --- TABLE LOGIC (Filtered by Search + Category ONLY, Dates ignored) ---
        $filteredCollection = $baseCol;
        if ($this->searchTerm) {
            $filteredCollection = $filteredCollection->filter(function($item) {
                return str_contains(strtolower($item['id']), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['spk_number']), strtolower($this->searchTerm)) ||
                       str_contains(strtolower($item['customer_name']), strtolower($this->searchTerm));
            });
        }

        foreach ($this->filters as $key => $value) {
            if ($value) {
                $filteredCollection = $filteredCollection->where($key, $value);
            }
        }

        $totalResultsForTable = $filteredCollection->count();
        $totalPages = ceil($totalResultsForTable / $this->perPage);

        // Paginate manually for the table
        $paginatedItems = $filteredCollection->slice(($this->page - 1) * $this->perPage, $this->perPage)->all();

        return view('livewire.quality-control-index', [
            'paginatedItems'       => $paginatedItems,
            'totalResults'         => $totalResultsForTable,
            'w1_baseline'          => $w1_baseline,
            'w1_time'              => $w1_time,
            'w2_realtime_verified' => $w2_realtime_verified,
            'w3_total_backlog'     => $w3_total_backlog,
            'w4_shift_achievement' => $w4_shift_achievement,
            'totalPages'           => $totalPages,
            'currentPage'          => $this->page,
        ]);
    }
}
