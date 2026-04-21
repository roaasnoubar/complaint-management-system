<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ratting extends Model
{
    protected $table = 'rattings';

    protected $fillable = [
        'complain_id',
        'user_id',
        'authority_id',
        'response_speed_score',
        'comment',
    ];

    protected $casts = [
        'response_speed_score' => 'integer',
    ];

    public function complain(): BelongsTo
    {
        return $this->belongsTo(Complain::class, 'complain_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }
}