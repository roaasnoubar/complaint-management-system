<?php

namespace App\Http\Controllers;

use App\Models\ComplainChat;
use App\Models\CantMessage;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function getChat(Request $request, $complainId): JsonResponse
    {
        $complain = Complain::findOrFail($complainId);

        $chat = ComplainChat::with(['messages.sender'])
                            ->where('complain_id', $complainId)
                            ->first();

        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'chat_id'    => $chat->id,
                'complain_id'=> $chat->complain_id,
                'is_open'    => $chat->is_open,
                'closed_at'  => $chat->closed_at,
                'messages'   => $chat->messages->map(function ($message) {
                    return [
                        'id'        => $message->id,
                        'message'   => $message->message,
                        'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
                        'file_type' => $message->file_type,
                        'sent_at'   => $message->sent_at,
                        'sender'    => [
                            'id'   => $message->sender->id,
                            'name' => $message->sender->name,
                        ],
                    ];
                }),
            ],
        ], 200);
    }

    public function sendMessage(Request $request, $complainId): JsonResponse
    {
        $complain = Complain::findOrFail($complainId);
        $chat     = ComplainChat::where('complain_id', $complainId)->first();

        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }

        if (!$chat->is_open) {
            return response()->json(['success' => false, 'message' => 'Chat is closed.'], 422);
        }

        $request->validate([
            'message' => 'nullable|string|required_without:file',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:20480|required_without:message',
        ]);

        $filePath = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store('chats/' . $chat->id, 'public');
            $fileType = $file->getClientOriginalExtension();
        }

        $message = CantMessage::create([
            'chat_id'   => $chat->id,
            'sender_id' => $request->user()->id,
            'message'   => $request->message,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'sent_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data'    => [
                'id'        => $message->id,
                'message'   => $message->message,
                'file_path' => $filePath ? asset('storage/' . $filePath) : null,
                'file_type' => $fileType,
                'sent_at'   => $message->sent_at,
                'sender'    => [
                    'id'   => $request->user()->id,
                    'name' => $request->user()->name,
                ],
            ],
        ], 201);
    }

    public function getAllChats(Request $request): JsonResponse
    {
        $chats = ComplainChat::with(['complain', 'user', 'messages'])
                             ->latest()
                             ->paginate(10);

        return response()->json(['success' => true, 'data' => $chats], 200);
    }
}