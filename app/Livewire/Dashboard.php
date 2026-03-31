<?php

namespace App\Livewire;

use App\Services\CalculationService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $selectedMonth = '';

    public function mount(): void
    {
        if (empty($this->selectedMonth)) {
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }
    }



    #[Computed]
    public function kpis(): array
    {
        $dashboardService = app(DashboardService::class);
        return $dashboardService->getKpis($this->selectedMonth . '-01');
    }

    #[Computed]
    public function dailyReports()
    {
        $dashboardService = app(DashboardService::class);
        return $dashboardService->getDailyReports($this->selectedMonth . '-01');
    }

    public function formatCurrency($amount): string
    {
        return app(CalculationService::class)->formatCurrency((float) $amount);
    }

    public function formatPercentage($amount): string
    {
        return app(CalculationService::class)->formatPercentage((float) $amount);
    }

    public function getRoasIndicator($current, $target): string
    {
        return app(CalculationService::class)->getRoasIndicator((float) $current, (float) $target);
    }

    public function getFormula(string $metric): string
    {
        return app(CalculationService::class)->getFormula($metric);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
