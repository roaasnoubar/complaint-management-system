<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CantMessage extends Model
{
    protected $table = 'cant_messages';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'file_path',
        'file_type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(ComplainChat::class, 'chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}