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
        $month = Carbon::now()->startOfMonth();

        // Monthly Setting (Base for Current Month) - Using updateOrCreate for idempotency
        MonthlySetting::updateOrCreate(
            ['month' => $month->format('Y-m-01')],
            [
                'target_revenue' => 500000000,
                'total_budget' => 75000000,
                'total_days' => $month->daysInMonth,
                'total_holidays' => 5,
            ]
        );

        // Weekly Targets (Base for Weeks 1-4)
        $weeklyTargets = [
            ['week' => 1, 'target_revenue' => 100000000, 'target_chat_consul' => 120, 'target_roas' => 6.5],
            ['week' => 2, 'target_revenue' => 125000000, 'target_chat_consul' => 150, 'target_roas' => 6.5],
            ['week' => 3, 'target_revenue' => 125000000, 'target_chat_consul' => 150, 'target_roas' => 7.0],
            ['week' => 4, 'target_revenue' => 150000000, 'target_chat_consul' => 180, 'target_roas' => 7.0],
        ];

        foreach ($weeklyTargets as $wt) {
            WeeklyTarget::updateOrCreate(
                ['month' => $month->format('Y-m-01'), 'week' => $wt['week']],
                $wt
            );
        }
    }
}
