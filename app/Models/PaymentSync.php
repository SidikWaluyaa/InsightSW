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

    public function getPaymentTypeLabelAttribute()
    {
        return match ($this->payment_type) {
            'BEFORE' => 'DP / AWAL',
            'AFTER' => 'LUNAS / AKHIR',
            'TAMBAH_JASA' => 'TAMBAH JASA',
            'LUNAS_AWAL' => 'LUNAS AWAL',
            'ONGKIR' => 'ONGKIR',
            default => $this->payment_type,
        };
    }

    public function getPaymentTypeBadgeClassAttribute()
    {
        return match ($this->payment_type) {
            'BEFORE' => 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600',
            'AFTER' => 'bg-blue-100 dark:bg-blue-500/10 text-blue-600',
            'TAMBAH_JASA' => 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600',
            'LUNAS_AWAL' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-600',
            'ONGKIR' => 'bg-rose-100 dark:bg-rose-500/10 text-rose-600',
            default => 'bg-slate-100 dark:bg-slate-500/10 text-slate-600',
        };
    }
}
