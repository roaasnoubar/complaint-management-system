<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Models\Notification;

class NotificationService
{
    /**
     * Create a notification and broadcast it in real-time.
     */
    public function send(int $userId, string $title, string $message, string $type): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);

        // Broadcast real-time event via Laravel Broadcasting
        broadcast(new NotificationSent($notification))->toOthers();

        return $notification;
    }

    // ─── Convenience helpers (called from Observers / Events) ───────────────

    public function complaintSubmitted(int $employeeId, string $complainTitle): void
    {
        $this->send(
            $employeeId,
            'New complaint assigned',
            "A new complaint has been submitted: \"{$complainTitle}\".",
            Notification::TYPE_COMPLAINT_SUBMITTED
        );
    }

    public function complaintAssigned(int $userId, string $complainTitle, string $departmentName): void
    {
        $this->send(
            $userId,
            'Your complaint was assigned',
            "Your complaint \"{$complainTitle}\" has been assigned to {$departmentName}.",
            Notification::TYPE_COMPLAINT_ASSIGNED
        );
    }

    public function statusChanged(int $userId, string $complainTitle, string $newStatus): void
    {
        $this->send(
            $userId,
            'Complaint status updated',
            "Your complaint \"{$complainTitle}\" status changed to \"{$newStatus}\".",
            Notification::TYPE_STATUS_CHANGED
        );
    }

    public function newMessage(int $recipientId, string $senderName, string $complainTitle): void
    {
        $this->send(
            $recipientId,
            'New message received',
            "{$senderName} sent a message on complaint \"{$complainTitle}\".",
            Notification::TYPE_NEW_MESSAGE
        );
    }

    public function escalated(int $userId, string $complainTitle, string $escalatedTo): void
    {
        $this->send(
            $userId,
            'Complaint escalated',
            "Your complaint \"{$complainTitle}\" has been escalated to {$escalatedTo}.",
            Notification::TYPE_ESCALATED
        );
    }

    public function resolved(int $userId, string $complainTitle): void
    {
        $this->send(
            $userId,
            'Complaint resolved',
            "Your complaint \"{$complainTitle}\" has been resolved.",
            Notification::TYPE_RESOLVED
        );
    }
}
