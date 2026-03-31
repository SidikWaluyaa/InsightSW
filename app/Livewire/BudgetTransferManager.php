<?php

namespace App\Livewire;

use App\Services\BudgetService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BudgetTransferManager extends Component
{
    public string $date;
    public string $amount = '';
    public string $description = '';
    public string $selectedMonth;

    public $transfers;
    public float $totalTransfers = 0;
    public float $effectiveBudget = 0;

    protected $rules = [
        'date' => 'required|date',
        'amount' => 'required|numeric',
        'description' => 'nullable|string|max:255',
    ];

    public function mount(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->selectedMonth = Carbon::now()->format('Y-m');
        $this->loadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(BudgetService::class);
        $month = $this->selectedMonth . '-01';

        $this->transfers = $service->getTransfers($month);
        $this->totalTransfers = $service->getTotalTransfers($month);
        $this->effectiveBudget = $service->getEffectiveBudget($month);
    }

    public function save(): void
    {
        $this->validate();

        $service = app(BudgetService::class);
        $service->createTransfer([
            'date' => $this->date,
            'amount' => $this->amount,
            'description' => $this->description,
        ]);

        $this->resetForm();
        $this->loadData();
        $this->dispatch('swal', [
            'title' => 'Berhasil!',
            'text' => 'Transfer anggaran berhasil dicatat!',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function delete(int $id): void
    {
        $service = app(BudgetService::class);
        $service->deleteTransfer($id);
        $this->loadData();
        $this->dispatch('swal', [
            'title' => 'Dihapus!',
            'text' => 'Transfer berhasil dihapus!',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function resetForm(): void
    {
        $this->date = Carbon::now()->format('Y-m-d');
        $this->amount = '';
        $this->description = '';
    }

    public function render()
    {
        return view('livewire.budget-transfer-manager');
    }
}
