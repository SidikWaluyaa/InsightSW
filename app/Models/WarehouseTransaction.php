<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
        'transaction_date' => 'datetime',
    ];
}
