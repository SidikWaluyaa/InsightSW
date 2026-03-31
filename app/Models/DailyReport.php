<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon $date
 * @property string $budgeting
 * @property string $spent
 * @property string $revenue
 * @property int $chat_in
 * @property int $chat_consul
 */
class DailyReport extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'date',
        'budgeting',
        'spent',
        'revenue',
        'chat_in',
        'chat_consul',
    ];

    protected $casts = [
        'date' => 'date',
        'budgeting' => 'decimal:2',
        'spent' => 'decimal:2',
        'revenue' => 'decimal:2',
        'chat_in' => 'integer',
        'chat_consul' => 'integer',
    ];
}
