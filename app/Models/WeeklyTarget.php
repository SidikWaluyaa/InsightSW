<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyTarget extends Model
{
    protected $fillable = [
        'week',
        'month',
        'target_revenue',
        'target_chat_consul',
        'target_roas',
    ];

    protected $casts = [
        'month' => 'date',
        'week' => 'integer',
        'target_revenue' => 'decimal:2',
        'target_chat_consul' => 'integer',
        'target_roas' => 'decimal:2',
    ];
}
