<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon $month
 * @property string $target_revenue
 * @property string $total_budget
 * @property int $total_days
 * @property int $total_holidays
 * @property-read int $working_days
 */
class MonthlySetting extends Model
{
    protected $fillable = [
        'month',
        'target_revenue',
        'total_budget',
        'total_days',
        'total_holidays',
    ];

    protected $casts = [
        'month' => 'date',
        'target_revenue' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'total_days' => 'integer',
        'total_holidays' => 'integer',
    ];

    public function getWorkingDaysAttribute(): int
    {
        return $this->total_days - $this->total_holidays;
    }
}
