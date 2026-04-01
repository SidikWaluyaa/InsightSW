<?php

namespace App\Livewire;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DailyReportForm extends Component
{
    public string $date;
    public string $budgeting = '';
    public string $spent = '';
    public string $revenue = '';
    public string $chat_in = '';
    public string $chat_consul = '';
    public bool $isSyncing = false;

    public $reports;
    public string $selectedMonth;
    public ?int $editingId = null;

    protected $rules = [
        'date' => 'required|date',
        'budgeting' => 'required|numeric|min:0',
        'spent' => 'required|numeric|min:0',
        'revenue' => 'required|numeric|min:0',
        'chat_in' => 'required|integer|min:0',
        'chat_consul' => 'required|integer|min:0',
    ];

    public function mount(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->selectedMonth = Carbon::now()->format('Y-m');
        $this->loadReports();
        $this->syncApiData();
    }

    public function updatedDate(): void
    {
        $this->syncApiData();
    }

    public function syncApiData(): void
    {
        $this->isSyncing = true;
        
        try {
            // 1. Fetch Meta Ads Spend
            $metaService = app(\App\Services\MetaAdsService::class);
            $adAccountId = 'act_1922369221497688';
            $metaData = $metaService->fetchSummary($adAccountId, [
                'startDate' => $this->date,
                'endDate' => $this->date
            ]);
            
            if ($metaData) {
                $this->spent = (string)round($metaData['spend'] ?? 0);
            }

            // 2. Fetch Sleekflow Metrics
            $sleekflowService = app(\App\Services\SleekflowService::class);
            $sleekflowData = $sleekflowService->getAnalyticsData($this->date, $this->date);
            
            if ($sleekflowData) {
                $this->chat_in = (string)($sleekflowData['totalContacts'] ?? 0);
                $this->chat_consul = (string)($sleekflowData['totalKonsul'] ?? 0);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("DailyReportForm Sync Error: " . $e->getMessage());
        }

        $this->isSyncing = false;
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadReports();
    }

    public function loadReports(): void
    {
        $monthDate = Carbon::parse($this->selectedMonth . '-01');
        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $this->reports = DailyReport::whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function save(): void
    {
        $this->validate();

        DailyReport::updateOrCreate(
            ['date' => $this->date],
            [
                'budgeting' => $this->budgeting,
                'spent' => $this->spent,
                'revenue' => $this->revenue,
                'chat_in' => $this->chat_in,
                'chat_consul' => $this->chat_consul,
            ]
        );

        $this->resetFilters();
        $this->loadReports();
        $this->dispatch('report-saved');
        $this->dispatch('swal', [
            'title' => 'Berhasil!',
            'text' => 'Laporan harian berhasil disimpan!',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function edit(int $id): void
    {
        $report = DailyReport::findOrFail($id);
        $this->editingId = $id;
        $this->date = $report->date->format('Y-m-d');
        $this->budgeting = $report->budgeting;
        $this->spent = $report->spent;
        $this->revenue = $report->revenue;
        $this->chat_in = $report->chat_in;
        $this->chat_consul = $report->chat_consul;
    }

    public function delete(int $id): void
    {
        DailyReport::destroy($id);
        $this->loadReports();
        $this->dispatch('swal', [
            'title' => 'Dihapus!',
            'text' => 'Laporan berhasil dihapus!',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function resetFilters(): void
    {
        $this->editingId = null;
        $this->date = Carbon::now()->format('Y-m-d');
        $this->budgeting = '';
        $this->spent = '';
        $this->revenue = '';
        $this->chat_in = '';
        $this->chat_consul = '';
    }

    public function render()
    {
        return view('livewire.daily-report-form');
    }
}
