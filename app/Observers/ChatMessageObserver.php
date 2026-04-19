<?php

namespace App\Observers;

use App\Models\ComplainChat;
use App\Models\User;
use App\Services\NotificationService;

class ChatMessageObserver
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Triggered when a new chat message is stored.
     * Creates notifications for all participants except the sender.
     *
     * Works with both CantMessage (API) and ChatMessage (web) models.
     */
    public function created($message): void
    {
        $senderId = $message->sender_id ?? null;
        if (!$senderId) {
            return;
        }

        $senderName = $message->sender?->name ?? 'Someone';

        // Both CantMessage/ChatMessage define a `chat()` relation.
        $chat = $message->chat()->with('complain')->first();
        if (!$chat || !$chat->complain) {
            return;
        }

        $complainTitle = $chat->complain->title ?? '';
        $complainDepartmentId = $chat->complain->department_id ?? null;
        $complainOwnerId = $chat->complain->user_id ?? null;

        $recipientIds = collect();

        // Notify all participants that have a ComplainChat session for this complain.
        $participantIds = ComplainChat::query()
            ->where('complain_id', $chat->complain_id)
            ->pluck('user_id')
            ->unique()
            ->values();

        foreach ($participantIds as $recipientId) {
            if ((int) $recipientId === (int) $senderId) {
                continue;
            }

            $recipientIds->push((int) $recipientId);
        }

        // Always notify the complain owner (if they're not the sender).
        if ($complainOwnerId && (int) $complainOwnerId !== (int) $senderId) {
            $recipientIds->push((int) $complainOwnerId);
        }

        // And notify employees in the complain department (if available).
        if ($complainDepartmentId) {
            $employeeIds = User::query()
                ->where('department_id', $complainDepartmentId)
                ->where('id', '!=', $senderId)
                ->whereHas('role', fn ($q) => $q->where('name', 'employee'))
                ->pluck('id');

            $recipientIds = $recipientIds->merge($employeeIds);
        }

        $recipientIds
            ->unique()
            ->values()
            ->each(function (int $recipientId) use ($senderName, $complainTitle) {
                $this->notificationService->newMessage($recipientId, $senderName, $complainTitle);
            });
    }
}
