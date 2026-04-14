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
        'name',
        'email',
        'phone',
        'birthdate',
        'password',
        'verification',
        'verification_code',
        'verification_expires_at',
        'role_id',
        'authority_id',
        'department_id',
        'score',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'remember_token',
    ];

    protected $casts = [
        'verification'            => 'boolean',
        'is_active'               => 'boolean',
        'verification_expires_at' => 'datetime',
        'birthdate'               => 'date',
    ];

    public function getBirthdateAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role?->name === Role::EMPLOYEE;
    }

    public function isUser(): bool
    {
        return $this->role?->name === Role::USER;
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

    public function complains(): HasMany
    {
        return $this->hasMany(Complain::class);
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
        return $this->hasMany(CantMessage::class, 'sender_id');
    }

    public function rattings(): HasMany
    {
        return $this->hasMany(Ratting::class);
    }
}