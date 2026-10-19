<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris hasil survei alasan drop-off: periode sampel, alasan, dan jumlah chat dengan alasan itu.
 */
class CsDropoffSurvey extends Model
{
    /** Kategori alasan (PRD halaman 2 + presentasi Tim Data); "Lainnya" untuk sisanya. */
    public const ALASAN = [
        'Harga di luar budget',
        'Sepatu rusak parah (di luar kemampuan servis)',
        'Ongkir / lokasi jauh',
        'Chat drop / tidak merespons',
        'Sibuk / belum sempat kirim',
        'Tunggu gajian',
        'Ragu lama pengerjaan',
        'Batal terang-terangan',
        'Lainnya',
    ];

    protected $fillable = ['periode_mulai', 'periode_selesai', 'alasan', 'jumlah', 'catatan', 'created_by'];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'jumlah' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Survei yang periodenya beririsan dengan rentang [start, end]. */
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->whereDate('periode_mulai', '<=', $end)->whereDate('periode_selesai', '>=', $start);
    }
}
