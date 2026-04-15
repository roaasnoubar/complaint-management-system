<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'complain_id',
        'user_id',
        'file_path',
        'file_type',
    ];

    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}