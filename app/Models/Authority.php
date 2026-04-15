<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Authority extends Model
{
    protected $fillable = [
        'complain_id',
        'department_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function complains(): HasMany
    {
        return $this->hasMany(Complain::class, 'auth_id');
    }

    public function rattings(): HasMany
    {
        return $this->hasMany(Ratting::class);
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->rattings()->avg('response_speed_score') ?? 0, 1);
    }

    public function getTotalRatingsAttribute(): int
    {
        return $this->rattings()->count();
    }
}