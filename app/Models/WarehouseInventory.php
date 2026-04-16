<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseInventory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'source_last_updated' => 'datetime',
    ];
}
