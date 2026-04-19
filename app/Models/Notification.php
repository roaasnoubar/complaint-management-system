<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 
        'title', 
        'message', 
        'type',
        'data', 
        'is_read'
    ];
    
    protected $casts = [
        'data' => 'array',
    ];

    // Notification types as constants
    const TYPE_COMPLAINT_SUBMITTED = 'complaint_submitted';
    const TYPE_COMPLAINT_ASSIGNED  = 'complaint_assigned';
    const TYPE_STATUS_CHANGED      = 'status_changed';
    const TYPE_NEW_MESSAGE         = 'new_message';
    const TYPE_ESCALATED           = 'escalated';
    const TYPE_RESOLVED            = 'resolved';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
