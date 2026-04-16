<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseRequest;
use App\Services\WarehouseSyncService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

class WarehouseRequests extends Component
{
    public $search = '';
    public $statusFilter = 'all';

    public function syncRequests()
    {
        $service = new WarehouseSyncService();
        $result = $service->syncRequests();

        $this->dispatch('swal', [
            'title' => $result['success'] ? 'Sync Berhasil' : 'Sync Gagal',
            'text' => $result['message'],
            'icon' => $result['success'] ? 'success' : 'error',
            'timer' => 3000
        ]);
    }

    #[Computed]
    public function requests()
    {
        return WarehouseRequest::query()
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('spk_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    #[Computed]
    public function totalRequests()
    {
        return WarehouseRequest::count();
    }

    #[Computed]
    public function pendingCount()
    {
        return WarehouseRequest::where('status', 'PENDING')->count();
    }

    #[Computed]
    public function completedCount()
    {
        return WarehouseRequest::where('status', 'COMPLETED')->count();
    }

    #[Computed]
    public function statusList()
    {
        return WarehouseRequest::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->orderBy('status')
            ->pluck('status');
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.warehouse-requests');
    }
}
