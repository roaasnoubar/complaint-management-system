<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'phone',
        'email',
        'password',
        'role_id',
        'authority_id',
        'department_id',
        'is_verified',
        'verification_code',
        'verification_expires_at',
        'score',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'remember_token',
    ];

    protected $casts = [
        'is_verified'             => 'boolean',
        'is_active'               => 'boolean',
        'verification_expires_at' => 'datetime',
        'date_of_birth'           => 'date',
    ];

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function complainChats(): HasMany
    {
        return $this->hasMany(ComplainChat::class, 'user_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->role?->permissions()
            ->where('name', $permissionName)
            ->exists() ?? false;
    }
}