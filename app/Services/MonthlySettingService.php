<?php

namespace App\Services;

use App\Models\MonthlySetting;
use App\Models\WeeklyTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlySettingService
{
    /**
     * Save monthly setting and sync weekly targets within a transaction
     */
    public function saveMonthlySetting(array $data): void
    {
        $monthDate = Carbon::parse($data['month'])->startOfMonth();

        DB::transaction(function () use ($monthDate, $data) {
            // 1. Update or Create Monthly Setting
            MonthlySetting::updateOrCreate(
                ['month' => $monthDate->format('Y-m-d')],
                [
                    'target_revenue' => $data['target_revenue'],
                    'total_budget' => $data['budget'],
                    'total_days' => $data['total_days'],
                    'total_holidays' => $data['total_holidays'],
                ]
            );

            // 2. Sync Weekly Targets
            $this->syncWeeklyTargets($monthDate, $data);
        });
    }

    /**
     * Sync weekly targets for a given month
     */
    protected function syncWeeklyTargets(Carbon $monthDate, array $data): void
    {
        $endOfMonth = $monthDate->copy()->endOfMonth();
        $weeksInMonth = $this->countWeeksInMonth($monthDate);

        // Remove existing weekly targets for this month to ensure clean sync
        WeeklyTarget::where('month', $monthDate->format('Y-m-d'))
            ->delete();

        $currentDate = $monthDate->copy();
        $weekNumber = 1;

        while ($currentDate->lte($endOfMonth)) {
            WeeklyTarget::create([
                'month' => $monthDate->format('Y-m-d'),
                'week' => $weekNumber,
                'target_revenue' => round($data['target_revenue'] / max(1, $weeksInMonth)),
                'target_roas' => $data['target_roas'],
                'target_chat_consul' => round($data['target_chat_consul'] / max(1, $weeksInMonth)),
            ]);

            $endDay = $this->getEndDayOfIteration($currentDate->day, $endOfMonth->day);
            $currentDate = $currentDate->copy()->day($endDay)->addDay();
            $weekNumber++;
        }
    }

    /**
     * Count how many weeks are defined for this month (custom logic)
     */
    protected function countWeeksInMonth(Carbon $monthDate): int
    {
        $endOfMonth = $monthDate->copy()->endOfMonth();
        $currentDate = $monthDate->copy();
        $weeks = 0;

        while ($currentDate->lte($endOfMonth)) {
            $weeks++;
            $endDay = $this->getEndDayOfIteration($currentDate->day, $endOfMonth->day);
            $currentDate = $currentDate->copy()->day($endDay)->addDay();
        }

        return $weeks;
    }

    /**
     * Helper to get the end day of a "week" iteration (7, 14, 21, 28, or end of month)
     */
    protected function getEndDayOfIteration(int $currentDay, int $monthEndDay): int
    {
        if ($currentDay <= 7) return 7;
        if ($currentDay <= 14) return 14;
        if ($currentDay <= 21) return 21;
        if ($currentDay <= 28) return 28;
        return $monthEndDay;
    }
}
