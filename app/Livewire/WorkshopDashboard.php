<?php

namespace App\Livewire;

use Livewire\Component;

use App\Services\WorkshopSyncService;
use App\Models\WorkshopMatrix;
use App\Models\WorkshopMetric;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WorkshopDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $activeFilter = 'bulan_ini';
    public $isSyncing = false;
    public $lastGlobalSyncTrigger = 0;

    public function mount()
    {
        $this->applyFilter('bulan_ini', false);
    }

    /**
     * Apply a preset or custom date filter.
     * 
     * @param string $filter Filter key: hari_ini, 7_hari, bulan_ini, 3_bulan, tahun_ini, minggu_ini, kemarin, 30_hari
     * @param bool $doSync Whether to trigger sync after applying the filter
     */
    public function applyFilter($filter, $doSync = true)
    {
        $this->activeFilter = $filter;

        switch ($filter) {
            case 'hari_ini':
                $this->startDate = now()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'kemarin':
                $this->startDate = now()->subDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->format('Y-m-d');
                break;
            case 'minggu_ini':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '7_hari':
                $this->startDate = now()->subDays(6)->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'bulan_ini':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '30_hari':
                $this->startDate = now()->subDays(29)->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case '3_bulan':
                $this->startDate = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'tahun_ini':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
        }

        if ($doSync) {
            $this->sync();
        }
    }

    /**
     * When user manually changes start/end date, switch to 'kustom' mode.
     */
    public function updatedStartDate()
    {
        $this->activeFilter = 'kustom';
        $this->sync();
    }

    public function updatedEndDate()
    {
        $this->activeFilter = 'kustom';
        $this->sync();
    }

    public function checkSync()
    {
        // Detect if a global sync happened in another tab (Meta Ads, etc.)
        $globalSyncTrigger = (int) \Illuminate\Support\Facades\Cache::get('global_sync_trigger', 0);
        if ($globalSyncTrigger > $this->lastGlobalSyncTrigger) {
            $this->lastGlobalSyncTrigger = $globalSyncTrigger;
            // Native re-render will pick up new computed properties
        }
    }

    public function sync($silent = false)
    {
        $this->isSyncing = true;
        
        try {
            $service = app(WorkshopSyncService::class);
            $result = $service->sync($this->startDate, $this->endDate);

            // Also sync Marketing data (Meta Ads, etc.) for the same period
            try {
                app(\App\Services\MarketingSyncService::class)->syncRange($this->startDate, $this->endDate);
            } catch (\Exception $e) {
                Log::warning("Marketing sync failed during Workshop Dashboard sync: " . $e->getMessage());
                // Don't fail the whole process if marketing sync fails
            }

            if ($result['success']) {
                $freshMetrics = WorkshopMetric::latest('last_sync_at')->first();
                
                // Trigger immediate reload for this component
                $this->dispatch('revenue-data-updated', metrics: $freshMetrics);
                
                // Trigger cross-tab reload for other dashboards (Meta Ads, etc.)
                \Illuminate\Support\Facades\Cache::put('global_sync_trigger', now()->timestamp, now()->addMinutes(10));
                
                if (!$silent) {
                    $this->dispatch('swal', [
                        'title' => 'Sinkronisasi Berhasil',
                        'text' => 'Data operasional Workshop telah diperbarui.',
                        'icon' => 'success'
                    ]);
                }
            } else {
                if (!$silent) {
                    $this->dispatch('swal', [
                        'title' => 'Sinkronisasi Gagal',
                        'text' => $result['message'],
                        'icon' => 'error'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Workshop Dashboard Sync Error: " . $e->getMessage());
        }

        $this->isSyncing = false;
    }

    #[Computed]
    public function matrix()
    {
        return WorkshopMatrix::all()->groupBy('phase');
    }

    #[Computed]
    public function metrics()
    {
        return WorkshopMetric::latest('last_sync_at')->first() ?? new WorkshopMetric();
    }

    /**
     * Get trend data in a structured format for the new UI.
     */
    #[Computed]
    public function trendAnalytics()
    {
        $trends = $this->metrics->trends ?? [];
        if (empty($trends) || !isset($trends['labels'])) {
            return null;
        }

        $inflow = collect($trends['inflow'] ?? []);
        $completion = collect($trends['completion'] ?? []);
        $labels = $trends['labels'] ?? [];

        $totalDays = count($labels);
        $isWeekly = $totalDays > 7;

        $totalIn = $inflow->sum();
        $totalOut = $completion->sum();
        $gap = $totalIn - $totalOut;
        
        // Find peak day
        $maxInValue = $inflow->max();
        $maxInIndex = $inflow->search($maxInValue);
        $peakDay = $labels[$maxInIndex] ?? null;

        // Adaptive Data Preparation
        $items = [];
        foreach ($labels as $idx => $label) {
            $items[] = [
                'date' => $label,
                'in' => intval($inflow[$idx] ?? 0),
                'out' => intval($completion[$idx] ?? 0)
            ];
        }

        $displayItems = [];
        if ($isWeekly) {
            // Group by 7-day chunks (Weeks)
            $chunks = collect($items)->chunk(7);
            foreach ($chunks as $index => $chunk) {
                $weekIn = $chunk->sum('in');
                $weekOut = $chunk->sum('out');
                $firstDay = $chunk->first()['date'];
                $lastDay = $chunk->last()['date'];
                
                $displayItems[] = $this->calculateStatusMetadata([
                    'label' => "Minggu " . ($index + 1),
                    'sub_label' => \Carbon\Carbon::parse($firstDay)->format('d M') . ' - ' . \Carbon\Carbon::parse($lastDay)->format('d M'),
                    'in' => $weekIn,
                    'out' => $weekOut
                ]);
            }
        } else {
            // Daily items
            foreach ($items as $item) {
                $displayItems[] = $this->calculateStatusMetadata([
                    'label' => \Carbon\Carbon::parse($item['date'])->format('D, d M'),
                    'sub_label' => 'Daily Status',
                    'in' => $item['in'],
                    'out' => $item['out']
                ]);
            }
        }

        return (object) [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'gap' => $gap,
            'peak_day' => $peakDay,
            'peak_value' => $maxInValue,
            'is_weekly' => $isWeekly,
            'pulse' => array_reverse($displayItems), // Show latest first
            'performance_index' => $trends['summary']['performance_index'] ?? '0%',
        ];
    }

    /**
     * Helper to calculate human status and metadata for a data point.
     */
    private function calculateStatusMetadata($data)
    {
        $in = $data['in'];
        $out = $data['out'];
        $ratio = $in > 0 ? ($out / $in) * 100 : ($out > 0 ? 100 : 0);

        // Flow Balance Logic (Indonesian)
        if ($out > $in) {
            $status = __('PENGEJARAN');
            $statusIcon = '🚀';
            $statusColor = 'emerald';
        } elseif ($out === $in && $in > 0) {
            $status = __('STABIL');
            $statusIcon = '⚖️';
            $statusColor = 'indigo';
        } elseif ($in > $out) {
            $status = __('PENUMPUKAN');
            $statusIcon = '📥';
            $statusColor = 'amber';
        } else {
            $status = __('IDLE');
            $statusIcon = '💤';
            $statusColor = 'slate';
        }

        return [
            'label' => $data['label'],
            'sub_label' => $data['sub_label'],
            'in' => $in,
            'out' => $out,
            'delta' => $in - $out,
            'status' => $status,
            'status_icon' => $statusIcon,
            'status_color' => $statusColor,
            'ratio' => min(100, $ratio)
        ];
    }

    /**
     * Get a human-readable label for the active filter period.
     */
    #[Computed]
    public function filterLabel()
    {
        $labels = [
            'hari_ini' => 'Hari Ini',
            'kemarin' => 'Kemarin',
            'minggu_ini' => 'Minggu Ini',
            '7_hari' => '7 Hari Terakhir',
            'bulan_ini' => 'Bulan Ini',
            '30_hari' => '30 Hari Terakhir',
            '3_bulan' => '3 Bulan Terakhir',
            'tahun_ini' => 'Tahun Ini',
            'kustom' => 'Kustom',
        ];

        return $labels[$this->activeFilter] ?? 'Kustom';
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.workshop-dashboard');
    }
}
