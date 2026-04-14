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

    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CantMessage::class, 'chat_id');
    }
}