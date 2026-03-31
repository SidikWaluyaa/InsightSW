<?php

namespace App\Services;

use App\Models\BudgetTransfer;
use App\Models\MonthlySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Get total transfers for a given month
     */
    public function getTotalTransfers(string $month): float
    {
        $monthDate = Carbon::parse($month);

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        return (float) BudgetTransfer::whereBetween('date', [$start, $end])
            ->sum('amount');
    }

    /**
     * Get effective budget (total_budget + transfers)
     */
    public function getEffectiveBudget(string $month): float
    {
        $monthDate = Carbon::parse($month);

        $setting = MonthlySetting::where('month', $monthDate->startOfMonth()->format('Y-m-d'))
            ->first();

        if (!$setting) {
            return 0;
        }

        $totalBudget = (float) $setting->total_budget;
        $totalTransfers = $this->getTotalTransfers($month);

        return $totalBudget + $totalTransfers;
    }

    /**
     * Create a new budget transfer
     */
    public function createTransfer(array $data): BudgetTransfer
    {
        return BudgetTransfer::create([
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Get all transfers for a given month
     */
    public function getTransfers(string $month): \Illuminate\Database\Eloquent\Collection
    {
        $monthDate = Carbon::parse($month);

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        return BudgetTransfer::whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Delete a transfer
     */
    public function deleteTransfer(int $id): bool
    {
        return BudgetTransfer::destroy($id) > 0;
    }
}
