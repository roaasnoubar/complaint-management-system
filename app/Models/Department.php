<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'authority_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function complains(): HasMany
    {
        return $this->hasMany(Complain::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}