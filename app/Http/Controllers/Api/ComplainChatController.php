<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ComplainChat;
use App\Models\Complain; // تأكدي من اسم الموديل Complain
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComplainChatController extends Controller
{
    /**
     * جلب رسائل الدردشة لشكوى معينة
     */
    public function show(Complain $complain): JsonResponse
    {
        // جلب أو إنشاء دردشة لهذه الشكوى (للمستخدم المسجل فقط)
        $chat = ComplainChat::firstOrCreate(
            ['complain_id' => $complain->id],
            [
                'user_id' => auth()->id(),
                'is_open' => true
            ]
        );

        // جلب الرسائل مع بيانات المرسل
        $chat->load(['messages.sender:id,name']);

        return response()->json([
            'success' => true,
            'chat_status' => $chat->is_open ? 'Open' : 'Closed',
            'data' => $chat
        ]);
    }

    /**
     * إرسال رسالة جديدة (من الأندرويد)
     */
    public function storeMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:complain_chats,id',
            'message' => 'required|string|max:2000',
        ]);

        $chat = ComplainChat::findOrFail($validated['chat_id']);

        // منع الإرسال إذا كانت الدردشة مغلقة
        if (!$chat->is_open) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، الدردشة مغلقة ولا يمكن إرسال رسائل.'
            ], 403);
        }

        $message = ChatMessage::create([
            'chat_id'   => $validated['chat_id'],
            'sender_id' => auth()->id(),
            'message'   => $validated['message'],
            'sent_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data'    => $message->load('sender:id,name')
        ], 201);
    }

    /**
     * إغلاق الدردشة (يمكن استدعاؤها عند حل الشكوى)
     */
    public function close(ComplainChat $chat): JsonResponse
    {
        $chat->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إغلاق الدردشة بنجاح.'
        ]);
    }
}