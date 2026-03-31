<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon $date
 * @property string $amount
 * @property string $description
 */
class BudgetTransfer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'date',
        'amount',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
