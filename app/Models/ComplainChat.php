<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplainChat extends Model
{
    protected $fillable = [
        'complain_id',
        'user_id',
        'is_open',
        'closed_at',
    ];

    protected $casts = [
        'is_open'   => 'boolean',
        'closed_at' => 'datetime',
    ];

    /**
     * العلاقة مع الشكوى الأساسية
     */
    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    /**
     * العلاقة مع صاحب الشكوى (مقدم الشكوى)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الرسائل
     * تنبيه: تأكدي أن اسم الموديل عندك هو ChatMessage 
     * إذا كان لا يزال CantMessage اتركيها كما هي، لكن الأفضل برمجياً توحيدها.
     */
    public function messages(): HasMany
    {
        // سأضع ChatMessage لأنه الاسم المعتمد في الكنترولر والميجريشن
        return $this->hasMany(ChatMessage::class, 'chat_id');
    }
}