<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComplainChat;
use App\Models\ChatMessage; // تأكدي أن اسم الموديل ChatMessage (أو غيريه لـ CantMessage حسب ملفك)
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * 1. جلب المحادثة ورسائلها (للطالب أو الموظف المخول)
     */
    public function getChat(Request $request, $complainId): JsonResponse
    {
        // جلب الشكوى أو إعطاء خطأ 404
        $complain = Complain::findOrFail($complainId);
        $user = auth()->user();

        // التأكد من أن المستخدم مخول (صاحب الشكوى أو موظف ضمن الصلاحية الزمنية)
        if (!$complain->canAccessChat($user) && $user->id !== $complain->user_id) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، ليس لديك صلاحية للوصول لهذه المحادثة حالياً.'
            ], 403);
        }

        // جلب المحادثة مع الرسائل وبيانات المرسلين
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
                'can_send'    => $complain->canAccessChat($user), // هام جداً للأندرويد لإظهار/إخفاء زر الإرسال
                'messages'    => $chat->messages->map(function ($message) {
                    return [
                        'id'        => $message->id,
                        'message'   => $message->message,
                        // تحويل مسار الملف إلى رابط كامل ليفتح في الأندرويد
                        'file_url'  => $message->file_path ? asset('storage/' . $message->file_path) : null,
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

    /**
     * 2. إرسال رسالة جديدة (نص + ملفات)
     */
    public function sendMessage(Request $request, $complainId): JsonResponse
    {
        $complain = Complain::findOrFail($complainId);
        $user = $request->user();
        $chat = ComplainChat::where('complain_id', $complainId)->first();

        // أ. التحقق من وجود الشات وأنه مفتوح
        if (!$chat || !$chat->is_open) {
            return response()->json(['success' => false, 'message' => 'المحادثة مغلقة ولا يمكن الإرسال.'], 422);
        }

        // ب. تطبيق منطق التصعيد والصلاحيات (5 أيام للموظف، 10 للمدير، ومفتوح لمدير الجهة)
        if (!$complain->canAccessChat($user)) {
            return response()->json([
                'success' => false, 
                'message' => 'ليس لديك صلاحية للمراسلة في هذه المرحلة (انتهت المدة أو تم التصعيد).'
            ], 403);
        }

        // ج. التحقق من مدخلات الأندرويد
        $request->validate([
            'message' => 'nullable|string|required_without:file',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:10240', // حد أقصى 10 ميجا
        ]);

        // د. معالجة رفع الملف (إن وجد)
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

    /**
     * 3. جلب جميع المحادثات (للمدير العام لرؤية النشاط)
     */
    // جلب كل المحادثات (للموظفين والمدراء فقط)
public function getAllChats(Request $request): JsonResponse
{
    $user = auth()->user();

    // منع المستخدم العادي من رؤية كل المحادثات
    if ($user->role_id == 3) {
        return response()->json(['success' => false, 'message' => 'غير مصرح لك'], 403);
    }

    $chats = ComplainChat::with(['complain.user', 'user']) // جلب بيانات الشكوى وصاحبها والموظف
                         ->latest()
                         ->paginate(15);

    return response()->json(['success' => true, 'data' => $chats], 200);
}

// فتح محادثة (للموظف)
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
}