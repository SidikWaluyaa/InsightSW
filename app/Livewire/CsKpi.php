<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\CsKpiApiService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('KPI CS - Leaderboard')]
class CsKpi extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public bool $isLoaded = false;
    public bool $isMock = false;
    public ?string $errorMessage = null;

    public array $summary = [];
    public array $perCs = [];
    public array $period = [];
    public array $metadata = [];

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public function mount()
    {
        $this->startDate = request()->query('startDate') ?? '';
        $this->endDate = request()->query('endDate') ?? '';

        // Default to current month if empty
        if (empty($this->startDate)) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
    }

    public function loadData()
    {
        $this->isLoaded = true;
        $this->fetchData();
    }

    public function updatedStartDate()
    {
        if ($this->isLoaded) {
            $this->fetchData();
        }
    }

    public function updatedEndDate()
    {
        if ($this->isLoaded) {
            $this->fetchData();
        }
    }

    public function updateDateRange(string $start, string $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;

        if ($this->isLoaded) {
            $this->fetchData();
        }
    }

    public function refreshData()
    {
        $cacheKey = "cs_kpi_data_{$this->startDate}_{$this->endDate}";
        Cache::forget($cacheKey);

        $this->fetchData();

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data performa KPI CS terbaru.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    protected function fetchData()
    {
        $cacheKey = "cs_kpi_data_{$this->startDate}_{$this->endDate}";

        try {
            $data = Cache::remember($cacheKey, 15, function () {
                $service = new CsKpiApiService();
                return $service->fetchKpiData($this->startDate, $this->endDate);
            });

            if ($data) {
                $this->summary = $data['summary'] ?? [];
                $this->perCs = $data['per_cs'] ?? [];
                $this->period = $data['period'] ?? [];
                $this->metadata = $data['metadata'] ?? [];
                $this->isMock = $data['is_mock'] ?? false;
                $this->errorMessage = null;
            } else {
                throw new \Exception("Gagal mengurai data KPI CS.");
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->summary = [];
            $this->perCs = [];
        }
    }

    public function render()
    {
        return view('livewire.cs-kpi');
    }
}
