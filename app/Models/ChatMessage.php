<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'file_path', // ضروري لرفع الصور
        'file_type', // ضروري لرفع الصور
        'sent_at',
    ];

    // طريقة الـ casts في النسخ الحديثة من لارافيل
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * العلاقة مع غرفة المحادثة
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(ComplainChat::class, 'chat_id');
    }

    /**
     * العلاقة مع مرسل الرسالة
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}