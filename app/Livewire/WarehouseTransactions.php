<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseTransaction;
use App\Services\WarehouseSyncService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

class WarehouseTransactions extends Component
{
    public $search = '';
    public $typeFilter = 'all';

    public function syncTransactions()
    {
        $service = new WarehouseSyncService();
        $result = $service->syncTransactions();

        $this->dispatch('swal', [
            'title' => $result['success'] ? 'Sync Berhasil' : 'Sync Gagal',
            'text' => $result['message'],
            'icon' => $result['success'] ? 'success' : 'error',
            'timer' => 3000
        ]);
    }

    #[Computed]
    public function transactions()
    {
        return WarehouseTransaction::query()
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter !== 'all', function($q) {
                $q->where('type', $this->typeFilter);
            })
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    #[Computed]
    public function totalTransactions()
    {
        return WarehouseTransaction::count();
    }

    #[Computed]
    public function inCount()
    {
        return WarehouseTransaction::where('type', 'IN')->count();
    }

    #[Computed]
    public function outCount()
    {
        return WarehouseTransaction::where('type', 'OUT')->count();
    }

    #[Computed]
    public function adjustmentCount()
    {
        return WarehouseTransaction::where('type', 'ADJUSTMENT')->count();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.warehouse-transactions');
    }
}
