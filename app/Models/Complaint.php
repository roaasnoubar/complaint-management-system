<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_number',
        'user_id',
        'authority_id',
        'department_id',
        'title',
        'description',
        'status',
        'assigned_level',
        'assigned_at',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
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
        // Auto-generate complaint number before creating
        static::creating(function (Complaint $complaint) {
            $complaint->complaint_number = self::generateComplaintNumber();
        });

        // Auto-create chat when complaint is created
        static::created(function (Complaint $complaint) {
            ComplainChat::create([
                'complaint_id' => $complaint->id,
                'user_id'      => $complaint->user_id,
                'is_open'      => true,
            ]);
        });

        // Auto-close chat when complaint is resolved
        static::updated(function (Complaint $complaint) {
            if ($complaint->status === self::STATUS_RESOLVED) {
                $chat = ComplainChat::where('complaint_id', $complaint->id)->first();
                if ($chat && $chat->is_open) {
                    $chat->update([
                        'is_open'   => false,
                        'closed_at' => now(),
                    ]);
                }
            }
        });
    }

    public static function generateComplaintNumber(): string
    {
        $year          = now()->year;
        $lastComplaint = self::orderBy('id', 'desc')->first();
        $nextNumber    = $lastComplaint ? ($lastComplaint->id + 1) : 1;

        return 'CMP-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function chat(): HasOne
    {
        return $this->hasOne(ComplainChat::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function canEscalate(): bool
    {
        if ($this->status === self::STATUS_RESOLVED) {
            return false;
        }

        if ($this->assigned_level <= self::LEVEL_HEAD) {
            return false;
        }

        $assignedAt = $this->assigned_at ?? $this->created_at;
        $daysPassed = $assignedAt->diffInDays(now());

        return $daysPassed >= self::ESCALATION_DAYS;
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