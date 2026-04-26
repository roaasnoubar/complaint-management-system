<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Complain extends Model
{
    protected $table = 'complains';

    protected $fillable = [
        'full_name',   
        'complain_number',
        'user_id',
        'authority_id',
        'department_id',
        'priority', 
        'current_department_id',
        'title',
        'description',
        'status',
        'is_valid',
        'assigned_level',
        'assigned_at',
        'resolved_at',
    ];

    // إضافة الحقول الوهمية للـ JSON لسهولة التعامل مع الأندرويد
    protected $appends = ['created_at_human', 'level_name', 'can_chat'];

    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_valid'    => 'boolean',
    ];

    const STATUS_PENDING     = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_RESOLVED    = 'Resolved';
    const STATUS_REJECTED    = 'Rejected';

    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING     => [self::STATUS_IN_PROGRESS, self::STATUS_REJECTED],
        self::STATUS_IN_PROGRESS => [self::STATUS_RESOLVED, self::STATUS_REJECTED],
        self::STATUS_RESOLVED    => [],
        self::STATUS_REJECTED    => [],
    ];

    /**
     * دالة التحقق من صلاحية المراسلة (كاملة لكل المستويات)
     */
    public function canAccessChat($user): bool
    {
        if (!$user) return false;

        // 1. إذا كانت الشكوى محلولة أو مرفوضة، يُغلق الشات للجميع
        if (in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED])) {
            return false;
        }

        // 2. صاحب الشكوى (Level 4 / User)
        if ($user->id === $this->user_id) {
            return true;
        }

        // حساب الأيام منذ تاريخ الإسناد لهذا المستوى
        $days = $this->assigned_at ? $this->assigned_at->diffInDays(now()) : 0;

        // 3. صلاحيات الموظفين والمدراء بناءً على المستوى الحالي للشكوى
        return match($user->role->level) {
            3 => ($this->assigned_level == 3 && $days <= 5),  // موظف: أول 5 أيام
            2 => ($this->assigned_level == 2 && $days <= 10), // مدير قسم: حتى اليوم 10
            1 => ($this->assigned_level == 1),                // مدير الجهة: لا سقف زمني
            default => false,
        };
    }

    // Accessor لاستخدام الدالة في الـ API كحقل can_chat
    public function getCanChatAttribute(): bool
    {
        return $this->canAccessChat(auth()->user());
    }

    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getLevelNameAttribute(): string
    {
        return match($this->assigned_level) {
            3       => 'Employee',
            2       => 'Department Manager',
            1       => 'Head of Organization',
            default => 'Unknown',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Complain $complain) {
            $complain->complain_number = self::generateComplainNumber();
            if (empty($complain->assigned_level)) {
                $complain->assigned_level = 3;
            }
            $complain->assigned_at = now();
            if (empty($complain->current_department_id)) {
                $complain->current_department_id = $complain->department_id;
            }
        });

        static::created(function (Complain $complain) {
            if (class_exists(ComplainChat::class)) {
                ComplainChat::create([
                    'complain_id' => $complain->id,
                    'user_id'     => $complain->user_id,
                    'is_open'     => true,
                ]);
            }
        });

        static::updated(function (Complain $complain) {
            if (in_array($complain->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED])) {
                $chat = ComplainChat::where('complain_id', $complain->id)->first();
                if ($chat && $chat->is_open) {
                    $chat->update([
                        'is_open'   => false,
                        'closed_at' => now(),
                    ]);
                }
                if (is_null($complain->resolved_at)) {
                    $complain->updateQuietly(['resolved_at' => now()]);
                }
            }
        });
    }

    public static function generateComplainNumber(): string
    {
        $year       = now()->year;
        $last       = self::orderBy('id', 'desc')->first();
        $nextNumber = $last ? ($last->id + 1) : 1;
        return 'CMP-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function authority()
{
    return $this->belongsTo(Authority::class, 'authority_id');
}
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function attachments(): HasMany { return $this->hasMany(Attachment::class, 'complain_id'); }
    public function chat(): HasOne { return $this->hasOne(ComplainChat::class, 'complain_id'); }
}