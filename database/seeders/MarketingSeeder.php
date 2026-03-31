<?php

namespace Database\Seeders;

use App\Models\BudgetTransfer;
use App\Models\DailyReport;
use App\Models\MonthlySetting;
use App\Models\WeeklyTarget;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $month = Carbon::create(2026, 3, 1);

        // Monthly Setting
        MonthlySetting::create([
            'month' => $month->format('Y-m-01'),
            'target_revenue' => 500000000,
            'total_budget' => 75000000,
            'total_days' => 31,
            'total_holidays' => 5,
        ]);

        // Weekly Targets
        $weeklyTargets = [
            ['week' => 1, 'target_revenue' => 100000000, 'target_chat_consul' => 120, 'target_roas' => 6.5],
            ['week' => 2, 'target_revenue' => 125000000, 'target_chat_consul' => 150, 'target_roas' => 6.5],
            ['week' => 3, 'target_revenue' => 125000000, 'target_chat_consul' => 150, 'target_roas' => 7.0],
            ['week' => 4, 'target_revenue' => 150000000, 'target_chat_consul' => 180, 'target_roas' => 7.0],
        ];

        foreach ($weeklyTargets as $wt) {
            WeeklyTarget::create(array_merge($wt, ['month' => $month->format('Y-m-01')]));
        }

        // Daily Reports (March 1-25, 2026 — simulated data)
        $dailyData = [
            ['date' => '2026-03-02', 'budgeting' => 3000000, 'spent' => 2850000, 'revenue' => 18500000, 'chat_in' => 45, 'chat_consul' => 22],
            ['date' => '2026-03-03', 'budgeting' => 3000000, 'spent' => 2920000, 'revenue' => 21000000, 'chat_in' => 52, 'chat_consul' => 28],
            ['date' => '2026-03-04', 'budgeting' => 3000000, 'spent' => 3100000, 'revenue' => 15800000, 'chat_in' => 38, 'chat_consul' => 18],
            ['date' => '2026-03-05', 'budgeting' => 2800000, 'spent' => 2750000, 'revenue' => 22500000, 'chat_in' => 55, 'chat_consul' => 30],
            ['date' => '2026-03-06', 'budgeting' => 2800000, 'spent' => 2680000, 'revenue' => 19200000, 'chat_in' => 48, 'chat_consul' => 25],
            ['date' => '2026-03-09', 'budgeting' => 3200000, 'spent' => 3150000, 'revenue' => 24500000, 'chat_in' => 60, 'chat_consul' => 35],
            ['date' => '2026-03-10', 'budgeting' => 3200000, 'spent' => 3050000, 'revenue' => 20100000, 'chat_in' => 50, 'chat_consul' => 27],
            ['date' => '2026-03-11', 'budgeting' => 3000000, 'spent' => 2900000, 'revenue' => 23800000, 'chat_in' => 58, 'chat_consul' => 32],
            ['date' => '2026-03-12', 'budgeting' => 3000000, 'spent' => 2880000, 'revenue' => 17500000, 'chat_in' => 42, 'chat_consul' => 20],
            ['date' => '2026-03-13', 'budgeting' => 2900000, 'spent' => 2870000, 'revenue' => 26000000, 'chat_in' => 62, 'chat_consul' => 38],
            ['date' => '2026-03-16', 'budgeting' => 3100000, 'spent' => 3080000, 'revenue' => 28500000, 'chat_in' => 65, 'chat_consul' => 40],
            ['date' => '2026-03-17', 'budgeting' => 3100000, 'spent' => 2950000, 'revenue' => 22000000, 'chat_in' => 53, 'chat_consul' => 29],
            ['date' => '2026-03-18', 'budgeting' => 2800000, 'spent' => 2780000, 'revenue' => 19800000, 'chat_in' => 47, 'chat_consul' => 24],
            ['date' => '2026-03-19', 'budgeting' => 2800000, 'spent' => 2720000, 'revenue' => 25200000, 'chat_in' => 59, 'chat_consul' => 34],
            ['date' => '2026-03-20', 'budgeting' => 3000000, 'spent' => 2960000, 'revenue' => 21500000, 'chat_in' => 51, 'chat_consul' => 26],
            ['date' => '2026-03-23', 'budgeting' => 3200000, 'spent' => 3180000, 'revenue' => 30000000, 'chat_in' => 70, 'chat_consul' => 42],
            ['date' => '2026-03-24', 'budgeting' => 3200000, 'spent' => 3100000, 'revenue' => 27500000, 'chat_in' => 64, 'chat_consul' => 37],
            ['date' => '2026-03-25', 'budgeting' => 3000000, 'spent' => 2950000, 'revenue' => 23000000, 'chat_in' => 56, 'chat_consul' => 31],
        ];

        foreach ($dailyData as $d) {
            DailyReport::create($d);
        }

        // Budget Transfers
        BudgetTransfer::create([
            'date' => '2026-03-10',
            'amount' => 5000000,
            'description' => 'Tambahan budget dari management untuk campaign Ramadhan',
        ]);

        BudgetTransfer::create([
            'date' => '2026-03-18',
            'amount' => 3000000,
            'description' => 'Top-up budget untuk push closing akhir bulan',
        ]);
    }
}
