<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopMetric extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pipeline'        => 'array',
        'trends'          => 'array',
        'workload'        => 'array',
        'service_mix'     => 'array',
        'leaderboard'     => 'array',
        'urgent_orders'   => 'array',
        'stock_alerts'    => 'array',
        'recent_activity' => 'array',
        'last_sync_at'    => 'datetime',
    ];
}
