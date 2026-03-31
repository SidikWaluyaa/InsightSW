<?php

namespace App\Livewire;

use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WeeklyReportTable extends Component
{
    public string $selectedMonth;
    public array $weeks = [];
    public array $summary = [];

    public function mount(): void
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
        $this->loadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(ReportService::class);
        $month = $this->selectedMonth . '-01';

        $this->weeks = $service->getWeeklyReport($month);
        $this->summary = $service->getMonthlySummary($month);
    }

    public function render()
    {
        return view('livewire.weekly-report-table');
    }
}
