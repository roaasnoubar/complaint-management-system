<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComplainChat;
use App\Models\ChatMessage; 
use App\Http\Resources\ChatMessageResource;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    
    public function getChat(Request $request, $complainId): JsonResponse
    {
        // جلب الشكوى أو إعطاء خطأ 
        $complain = Complain::findOrFail($complainId);
        $user = auth()->user();

        if (!$complain->canAccessChat($user) && $user->id !== $complain->user_id) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، ليس لديك صلاحية للوصول لهذه المحادثة حالياً.'
            ], 403);
        }

        $chat = ComplainChat::with(['messages.sender:id,name'])
                            ->where('complain_id', $complainId)
                            ->first();

        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'المحادثة غير موجودة.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'chat_id'     => $chat->id,
                'complain_id' => $chat->complain_id,
                'is_open'     => $chat->is_open,
                'can_send'    => $complain->canAccessChat($user),
                
                'messages'    => ChatMessageResource::collection($chat->messages),
            ],
        ], 200);
    }



    public function sendMessage(Request $request, $complainId): JsonResponse
    {
        $complain = Complain::findOrFail($complainId);
        $user = $request->user();
        $chat = ComplainChat::where('complain_id', $complainId)->first();

        if (!$chat || !$chat->is_open) {
            return response()->json(['success' => false, 'message' => 'المحادثة مغلقة ولا يمكن الإرسال.'], 422);
        }

        if (!$complain->canAccessChat($user)) {
            return response()->json([
                'success' => false, 
                'message' => 'ليس لديك صلاحية للمراسلة في هذه المرحلة (انتهت المدة أو تم التصعيد).'
            ], 403);
        }

        $request->validate([
            'message' => 'nullable|string|required_without:file',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:10240', //  10 ميجا
        ]);

       
        $filePath = null; 
        $fileType = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // تخزين الملف في مجلد خاص بالمحادثة داخل storage/app/public/chats
            $filePath = $file->store('chats/' . $chat->id, 'public');
            $fileType = $file->getClientOriginalExtension();
        }

        // هـ. تخزين الرسالة في قاعدة البيانات
        $message = ChatMessage::create([
            'chat_id'   => $chat->id,
            'sender_id' => $user->id,
            'message'   => $request->message,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'sent_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح.',
            'data'    => [
                'id'       => $message->id,
                'message'  => $message->message,
                'file_url' => $filePath ? asset('storage/' . $filePath) : null,
                'sender'   => ['id' => $user->id, 'name' => $user->name],
            ]
        ], 201);
    }

    
public function getAllChats(Request $request): JsonResponse
{
    $user = auth()->user();

 

    if (!in_array($user->role_id, [1, 2])) {
        return response()->json(['success' => false, 'message' => 'غير مصرح لك'], 403);
    }

    $chats = ComplainChat::with(['complain.user', 'user']) // جلب بيانات الشكوى وصاحبها والموظف
                         ->latest()
                         ->paginate(15);

    return response()->json(['success' => true, 'data' => $chats], 200);
}


public function openChat(Request $request, $complainId)
{
    $user = auth()->user();

    // حماية الدالة: فقط الموظف (المسؤول) يمكنه الفتح
    if ($user->role_id == 3) {
        return response()->json([
            'success' => false,
            'message' => 'عذراً، الموظف المسؤول فقط هو من يمكنه بدء المحادثة.'
        ], 403);
    }

    // فتح المحادثة أو تحديثها
    $chat = \App\Models\ComplainChat::updateOrCreate(
        ['complain_id' => $complainId],
        [
            'user_id' => $user->id, 
            'is_open' => true       
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'تم فتح المحادثة بنجاح، يمكن البدء بالإرسال الآن.',
        'chat' => $chat
    ]);
}
public function markAsRead($complainId)
{
    $user = auth()->user();

  
    \App\Models\ChatMessage::whereHas('chat', function ($query) use ($complainId) {
        $query->where('complain_id', $complainId);
    })
    ->where('sender_id', '!=', $user->id) // فقط الرسائل التي لم يرسلها المستخدم الحالي
    ->where('is_read', false)
    ->update(['is_read' => true]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث حالة الرسائل إلى مقروءة.'
    ]);
}
}