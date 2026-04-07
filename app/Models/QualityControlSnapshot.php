<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityControlSnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'baseline_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'baseline_count' => 'integer',
    ];
}
