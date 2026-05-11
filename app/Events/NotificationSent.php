<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    /**
     * Broadcast on a private channel per user.
     * Frontend listens on: private-notifications.{user_id}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("notifications.{$this->notification->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->notification->id,
            'title'      => $this->notification->title,
            'message'    => $this->notification->message,
            'type'       => $this->notification->type,
            'is_read'    => $this->notification->is_read,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
