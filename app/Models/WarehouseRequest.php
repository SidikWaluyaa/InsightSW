<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'material_details' => 'array',
        'requested_at' => 'datetime',
        'source_last_updated' => 'datetime',
    ];
}
