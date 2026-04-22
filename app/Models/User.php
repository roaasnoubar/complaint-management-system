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
        'username',
         'email', 'phone', 'birthdate', 'password', 
        'verification_code', 'verification_expires_at', 'is_verified', 
        'role_id', 'authority_id', 'score', 'is_active', 'is_banned', 'false_complaints_count'
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'remember_token',
    ];

    protected $casts = [
        'is_verified'             => 'boolean',
        'is_active'               => 'boolean',
        'is_banned'               => 'boolean', 
        'verification_expires_at' => 'datetime',
        'birthdate'               => 'date',
        'score'                   => 'integer',
        'false_complaints_count'  => 'integer',
        'password'                => 'hashed', 
    ];

    // --- منطق Sprint 6: إدارة السكور والحظر ---
    
    /**
     * تعديل سكور المستخدم والتعامل مع حالات الحظر تلقائياً
     */
    public function adjustScoreByValidity(bool $isValid): void
    {
        if ($isValid) {
            // زيادة النقاط للشكاوى الصحيحة
            $this->increment('score', 10);
        } else {
            // خصم نقاط للشكاوى الكاذبة وزيادة العداد
            $this->decrement('score', 20);
            $this->increment('false_complaints_count');

            // تلقائياً: إذا وصلت الشكاوى الكاذبة لـ 3 يتم الحظر
            if ($this->false_complaints_count >= 3) {
                $this->update(['is_banned' => true, 'is_active' => false]);
            }
        }
    }

    // --- الدوال الموجودة مسبقاً ---

    
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role?->name === 'employee';
    }

    public function isUser(): bool
    {
        return $this->role?->name === 'user';
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->role?->permissions()
            ->where('name', $permissionName)
            ->exists() ?? false;
    }

    // العلاقات (Relationships)
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function authority()
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
}