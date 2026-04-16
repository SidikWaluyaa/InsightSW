<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSync extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'spk_number',
        'customer_name',
        'customer_phone',
        'amount_paid',
        'payment_type',
        'total_bill_snapshot',
        'balance_snapshot',
        'paid_at',
        'source_created_at',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'total_bill_snapshot' => 'float',
        'balance_snapshot' => 'float',
        'paid_at' => 'datetime',
        'source_created_at' => 'datetime',
    ];
}
