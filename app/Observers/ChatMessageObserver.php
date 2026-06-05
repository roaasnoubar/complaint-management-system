<?php

namespace App\Observers;

use App\Models\ChatMessage;
use App\Models\ComplainChat;
use App\Models\User;
use App\Services\NotificationService;

class ChatMessageObserver
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Triggered when a new chat message is created.
     * Notifies the other party in the conversation (student ↔ employee).
     */
    public function created(ChatMessage $message): void
    {
        $chat     = ComplainChat::with('complain')->find($message->chat_id);
        $complain = $chat?->complain;

        if (! $complain) {
            return;
        }

        $sender = User::find($message->sender_id);
        if (! $sender) {
            return;
        }

        // Determine recipient: if sender is the complaint owner, notify assigned employee(s), and vice versa
        if ($message->sender_id === $complain->user_id) {
            // Student sent message — notify employees in current department
            $recipients = User::where('department_id', $complain->current_department_id ?? $complain->department_id)
                ->where('user_id', '!=', $message->sender_id)
                ->get();
        } else {
            // Employee sent message — notify the complaint owner
            $recipients = User::where('user_id', $complain->user_id)->get();
        }

        foreach ($recipients as $recipient) {
            $this->notificationService->newMessage(
                $recipient->user_id,
                $sender->name,
                $complain->title
            );
        }
    }
}
