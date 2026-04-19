<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complain extends Model
{
    protected $table = 'complains';

    protected $fillable = [
        'complain_number',
        'user_id',
        'auth_id',
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

    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'is_valid'    => 'boolean',
    ];

    const STATUS_PENDING     = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_RESOLVED    = 'Resolved';

    const LEVEL_EMPLOYEE = 3;
    const LEVEL_MANAGER  = 2;
    const LEVEL_HEAD     = 1;

    const ESCALATION_DAYS = 5;

    const STATUS_TRANSITIONS = [
        'Pending'     => 'In Progress',
        'In Progress' => 'Resolved',
    ];

    protected static function booted(): void
    {
        static::creating(function (Complain $complain) {
            $complain->complain_number = self::generateComplainNumber();
        });

        static::created(function (Complain $complain) {
            ComplainChat::create([
                'complain_id' => $complain->id,
                'user_id'     => $complain->user_id,
                'is_open'     => true,
            ]);
        });

        static::updated(function (Complain $complain) {
            if ($complain->status === self::STATUS_RESOLVED) {
                $chat = ComplainChat::where('complain_id', $complain->id)->first();
                if ($chat && $chat->is_open) {
                    $chat->update([
                        'is_open'   => false,
                        'closed_at' => now(),
                    ]);
                }
            }
        });
    }

    public static function generateComplainNumber(): string
    {
        $year        = now()->year;
        $last        = self::orderBy('id', 'desc')->first();
        $nextNumber  = $last ? ($last->id + 1) : 1;
        return 'CMP-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'auth_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'complain_id');
    }

    public function chat(): HasOne
    {
        return $this->hasOne(ComplainChat::class, 'complain_id');
    }

    public function rattings(): HasMany
    {
        return $this->hasMany(Ratting::class, 'complain_id');
    }

    public function canEscalate(): bool
    {
        if ($this->status === self::STATUS_RESOLVED) return false;
        if ($this->assigned_level <= self::LEVEL_HEAD) return false;

        $assignedAt = $this->assigned_at ?? $this->created_at;
        return $assignedAt->diffInDays(now()) >= self::ESCALATION_DAYS;
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
}