<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Balasan pertama per percakapan per hari (lihat SleekflowReplyService).
 */
class SleekflowConversationReply extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'first_in_at' => 'datetime',
        'first_ai_at' => 'datetime',
        'first_auto_at' => 'datetime',
        'first_staff_at' => 'datetime',
    ];
}
