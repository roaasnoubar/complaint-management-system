<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    const TYPE_COMPLAINT_ASSIGNED = 'complaint_assigned';
    const TYPE_COMPLAINT_RESOLVED = 'complaint_resolved';
    const TYPE_COMPLAINT_ESCALATED = 'complaint_escalated';
    const TYPE_COMPLAINT_SUBMITTED = 'complaint_submitted';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_RESOLVED            = 'resolved';
    const TYPE_ESCALATED = 'escalated';
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    // علاقة الإشعار بالمستخدم
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

