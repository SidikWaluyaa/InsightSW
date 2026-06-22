<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Services\CsForecastingApiService;
use Illuminate\Support\Facades\Cache;

#[Layout('layouts.app')]
#[Title('KPI CS - Forecasting')]
class CsForecasting extends Component
{
    public int $year = 2026;
    public string $compareYear = '';
    public string $semesterFilter = 'full'; // 'full', 's1', 's2'
    public bool $isLoaded = false;
    public bool $isMock = false;
    public ?string $errorMessage = null;

    public array $forecastingData = [];
    public array $compareForecastingData = [];

    protected $queryString = [
        'year' => ['except' => 2026],
        'compareYear' => ['except' => ''],
        'semesterFilter' => ['except' => 'full'],
    ];

    public function mount()
    {
        $this->year = (int) (request()->query('year') ?? 2026);
        $this->compareYear = request()->query('compareYear') ?? '';
        $this->semesterFilter = request()->query('semesterFilter') ?? 'full';
    }

    public function loadData()
    {
        $this->isLoaded = true;
        $this->fetchData();
    }

    public function updatedYear()
    {
        if ($this->isLoaded) {
            $this->fetchData();
        }
    }

    public function updatedCompareYear()
    {
        if ($this->isLoaded) {
            $this->fetchData();
        }
    }

    public function setSemesterFilter(string $filter)
    {
        $this->semesterFilter = $filter;
    }

    public function refreshData()
    {
        $cacheKey = "cs_forecasting_data_{$this->year}";
        Cache::forget($cacheKey);

        if (!empty($this->compareYear)) {
            $compareCacheKey = "cs_forecasting_data_{$this->compareYear}";
            Cache::forget($compareCacheKey);
        }

        $this->fetchData();

        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data forecasting CS terbaru.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    protected function fetchData()
    {
        $service = new CsForecastingApiService();
        $cacheKey = "cs_forecasting_data_{$this->year}";

        try {
            $result = Cache::remember($cacheKey, 15, function () use ($service) {
                return $service->fetchForecastingData($this->year);
            });

            $this->forecastingData = $result['data'] ?? [];
            $this->isMock = $result['is_mock'] ?? false;
            $this->errorMessage = null;

            // Fetch comparison data if requested
            if (!empty($this->compareYear)) {
                $compareYearInt = (int) $this->compareYear;
                $compareCacheKey = "cs_forecasting_data_{$compareYearInt}";
                
                $compareResult = Cache::remember($compareCacheKey, 15, function () use ($service, $compareYearInt) {
                    return $service->fetchForecastingData($compareYearInt);
                });

                $this->compareForecastingData = $compareResult['data'] ?? [];
                
                // If either is mock, flag as mock
                if ($compareResult['is_mock'] ?? false) {
                    $this->isMock = true;
                }
            } else {
                $this->compareForecastingData = [];
            }

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->forecastingData = [];
            $this->compareForecastingData = [];
        }
    }

    public function render()
    {
        return view('livewire.cs-forecasting');
    }
}
