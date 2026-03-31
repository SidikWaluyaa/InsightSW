<?php

namespace App\Services;

class CalculationService
{
    /**
     * Calculate ROAS (Return on Ad Spend)
     */
    public function roas(float $revenue, float $spent): float
    {
        if ($spent == 0) {
            return 0;
        }

        return round($revenue / $spent, 2);
    }

    /**
     * Calculate Greeting Rate (Chat Konsul / Chat Masuk)
     */
    public function greetingRate(int $chatConsul, int $chatIn): float
    {
        if ($chatIn == 0) {
            return 0;
        }

        return round(($chatConsul / $chatIn) * 100, 2);
    }

    /**
     * Calculate Cost per Chat Konsul
     */
    public function costPerChatConsul(float $spent, int $chatConsul): float
    {
        if ($chatConsul == 0) {
            return 0;
        }

        return round($spent / $chatConsul, 2);
    }

    /**
     * Calculate Cost per Chat Masuk
     */
    public function costPerChatIn(float $spent, int $chatIn): float
    {
        if ($chatIn == 0) {
            return 0;
        }

        return round($spent / $chatIn, 2);
    }

    /**
     * Calculate Remaining Revenue needed to hit target
     */
    public function remainingRevenue(float $targetRevenue, float $currentRevenue): float
    {
        return max(0, $targetRevenue - $currentRevenue);
    }

    /**
     * Calculate Remaining Chat needed to hit target
     */
    public function remainingChat(int $targetChat, int $currentChat): int
    {
        return max(0, $targetChat - $currentChat);
    }

    /**
     * Calculate Remaining Revenue Per Day
     */
    public function remainingRevenuePerDay(float $remainingRevenue, int $remainingDays): float
    {
        if ($remainingDays <= 0) {
            return 0;
        }

        return round($remainingRevenue / $remainingDays, 2);
    }

    /**
     * Calculate Remaining Chat Per Day
     */
    public function remainingChatPerDay(int $remainingChat, int $remainingDays): float
    {
        if ($remainingDays <= 0) {
            return 0;
        }

        return round($remainingChat / $remainingDays, 2);
    }

    /**
     * Calculate Remaining Budget
     */
    public function remainingBudget(float $totalBudget, float $totalSpent): float
    {
        return max(0, $totalBudget - $totalSpent);
    }

    /**
     * Calculate Daily Budget Target
     */
    public function dailyBudgetTarget(float $remainingBudget, int $remainingDays): float
    {
        if ($remainingDays <= 0) {
            return 0;
        }

        return round($remainingBudget / $remainingDays, 2);
    }

    /**
     * Get KPI color indicator based on achievement percentage
     * Green: >= 90% of target
     * Yellow: >= 70% of target
     * Red: < 70% of target
     */
    public function getColorIndicator(float $actual, float $target): string
    {
        if ($target <= 0) {
            return 'gray';
        }

        $percentage = ($actual / $target) * 100;

        if ($percentage >= 90) {
            return 'green';
        }

        if ($percentage >= 70) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * Get ROAS color indicator
     * Green: ROAS >= target
     * Yellow: ROAS >= 80% of target
     * Red: ROAS < 80% of target
     */
    public function getRoasIndicator(float $currentRoas, float $targetRoas): string
    {
        if ($targetRoas <= 0) {
            return 'gray';
        }

        $percentage = ($currentRoas / $targetRoas) * 100;

        if ($percentage >= 100) {
            return 'green';
        }

        if ($percentage >= 80) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * Get Budget Usage indicator
     * Green: <= 80% budget used
     * Yellow: <= 95% budget used
     * Red: > 95% budget used
     */
    public function getBudgetIndicator(float $spent, float $totalBudget): string
    {
        if ($totalBudget <= 0) {
            return 'gray';
        }

        $percentage = ($spent / $totalBudget) * 100;

        if ($percentage <= 80) {
            return 'green';
        }

        if ($percentage <= 95) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * Format currency in Indonesian Rupiah
     */
    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format percentage
     */
    public function formatPercentage(float $value): string
    {
        return number_format($value, 2, ',', '.') . '%';
    }

    /**
     * Get formula description for a metric
     */
    public function getFormula(string $metric): string
    {
        return match ($metric) {
            'roas' => 'Omset / Biaya Iklan',
            'greeting_rate' => '(Chat Konsul / Chat Masuk) x 100%',
            'cost_per_chat_consul' => 'Biaya Iklan / Chat Konsul',
            'cost_per_chat_in' => 'Biaya Iklan / Chat Masuk',
            'remaining_budget' => 'Anggaran (Bulanan + Transfer) - Biaya Terpakai',
            'daily_budget_target' => 'Sisa Anggaran / Sisa Hari Kerja',
            'remaining_revenue' => 'Target Omset - Omset Saat Ini',
            'remaining_revenue_per_day' => 'Sisa Sasaran Omset / Sisa Hari Kerja',
            'remaining_chat' => 'Target Chat - Chat Konsul Saat Ini',
            'remaining_chat_per_day' => 'Sisa Target Chat / Sisa Hari Kerja',
            default => 'Formula tidak ditemukan',
        };
    }
}
