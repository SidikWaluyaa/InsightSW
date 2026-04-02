<?php

namespace App\Livewire;

use App\Models\FinanceSyncLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class FinanceSyncHistory extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        $logs = FinanceSyncLog::with('user')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        return view('livewire.finance-sync-history', [
            'logs' => $logs
        ]);
    }
}
