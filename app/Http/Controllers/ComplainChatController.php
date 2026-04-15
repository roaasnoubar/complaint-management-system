<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ComplainChat;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplainChatController extends Controller
{
    /**
     * Display the chat for a complaint.
     */
    public function show(Complaint $complaint): View
    {
        $complaint->load(['user', 'complainChats.user']);

        $chat = ComplainChat::firstOrCreate(
            [
                'complain_id' => $complaint->id,
                'user_id' => auth()->id() ?? 1,
            ],
            ['is_open' => true]
        );

        $chat->load(['messages.sender']);

        return view('chats.show', compact('complaint', 'chat'));
    }

    /**
     * Store a new message in the chat.
     */
    public function storeMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:complain_chats,id',
            'message' => 'required|string|max:2000',
        ]);

        ChatMessage::create([
            'chat_id' => $validated['chat_id'],
            'sender_id' => auth()->id() ?? 1,
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', __('Message sent.'));
    }

    /**
     * Close the chat.
     */
    public function close(ComplainChat $chat): RedirectResponse
    {
        $chat->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        return redirect()->route('complaints.show', $chat->complain_id)
            ->with('success', __('Chat closed.'));
    }
}
