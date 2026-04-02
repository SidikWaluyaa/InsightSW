<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceSync extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'spk_number',
        'customer_name',
        'customer_phone',
        'status_pembayaran',
        'spk_status',
        'amount_paid',
        'total_bill',
        'discount',
        'shipping_cost',
        'remaining_balance',
        'invoice_awal_url',
        'invoice_akhir_url',
        'estimasi_selesai',
        'source_created_at',
        'source_updated_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'total_bill' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'estimasi_selesai' => 'datetime',
        'source_created_at' => 'datetime',
        'source_updated_at' => 'datetime',
    ];
}
