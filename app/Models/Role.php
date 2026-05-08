<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',  
        'name',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    const ADMIN    = 'admin';
    const EMPLOYEE = 'employee';
    const USER     = 'citizen';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}