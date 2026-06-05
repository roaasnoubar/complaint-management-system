<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Mail\StatusChangedMail;
use App\Http\Resources\Api\ComplainResource;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ComplaintProcessingController extends Controller
{
   
    public function acceptAsValid($id): JsonResponse
    {
        $complaint = Complain::findOrFail($id);
        
        $complaint->update([
            'status' => Complain::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $student = $complaint->user; 
        
        if ($student) {
            $student->increment('score', 10); 
        }

        $this->sendStatusEmail($complaint, Complain::STATUS_RESOLVED);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول الشكوى كشكوى صحيحة، وتحديث الحالة، ورفع سكور الطالب بنجاح.',
            'data' => [
                'id' => $complaint->id,
                'current_status' => $complaint->status,
                'user_new_score' => $student ? $student->score : 0
            ]
        ], 200);
    }
        
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with(['user', 'authority', 'department'])->findOrFail($id);
     // 1. التحقق من الصلاحيات العامة (هل هو موظف، مدير، أو أدمن؟)
        if (!$user->isEmployee() && !$user->isAdmin() && !$user->isDeptManager() && !$user->isAuthorityManager()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access.'], 403);
        }

        if (!$user->isAdmin() && intval($user->role->level) < intval($complain->level)) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، هذه الشكوى في مستوى إداري أعلى من صلاحياتك ولا يمكنك معالجتها.'
            ], 403);
        }

        if ($user->isEmployee() || $user->isDeptManager()) {
            if (intval($complain->department_id) !== intval($user->department_id)) {
                return response()->json(['success' => false, 'message' => 'هذه الشكوى لا تتبع لقسمك.'], 403);
            }
        }

        // 4. التحقق من إمكانية تغيير الحالة (Logic)
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complain->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint is already resolved or in a final state.',
            ], 422);
        }

        $nextStatusValue = is_array($allowedNextStatus) ? $allowedNextStatus[0] : $allowedNextStatus;
        $previousStatus  = $complain->status;

        $complain->status = $nextStatusValue;
        
        $complain->assigned_level = $user->role->level; 

        if ($nextStatusValue === Complain::STATUS_IN_PROGRESS) {
            $complain->assigned_at = now(); 
        }

        if ($nextStatusValue === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
            if ($complain->user) {
                $complain->user->increment('score', 10); 
            }
        }

        $complain->save();

        $this->sendStatusEmail($complain, $nextStatusValue);
        
        return response()->json([
            'success' => true,
            'message' => "Status updated from {$previousStatus} to {$nextStatusValue}",
            'data'    => [
                'id'             => $complain->id,
                'current_status' => $complain->status,
                'assigned_level' => $complain->assigned_level, 
                'level_name'     => $user->role->name,     
                'user_new_score' => $complain->user ? $complain->user->score : null,
            ],
        ], 200);
    }
    
    public function reject(Request $request, $id)
    {
        // 1. تحديث الشكوى (بإمكانك استخدام Eloquent بدلاً من DB::statement ليكون الكود أنظف)
        $complain = \App\Models\Complain::find($id);
        if (!$complain) return response()->json(['message' => 'الشكوى غير موجودة'], 404);
    
        $complain->update([
            'status' => 'Rejected',
            'notes' => $request->rejection_reason ?? 'لا يوجد سبب'
        ]);
    
        // 2. إنشاء الإشعار باستخدام الموديل (بدل DB::table)
        $notification = \App\Models\Notification::create([
            'user_id'    => $complain->user_id,
            'title'      => 'تم رفض الشكوى',
            'message'    => 'تم رفض شكواك رقم ' . $id . ' للأسباب التالية: ' . ($request->rejection_reason ?? 'لا يوجد سبب'),
            'is_read'    => false,
            'type'       => 'reject',
        ]);
    
        // 3. الخطوة السحرية: إطلاق الحدث يدوياً لكي يصل الإشعار للموبايل فوراً
        event(new \App\Events\NotificationSent($notification));
    
        return response()->json(['message' => 'تم الرفض بنجاح وتم إرسال إشعار للمستخدم']);
        $authority = \App\Models\Authority::find($complain->auth_id); // أو $complain->auth_id حسب عمودك
if ($authority) {
    // خصم 10 نقاط مثلاً من السكور الإجمالي أو المتوسط
    $authority->decrement('total_score', 10); 
    // ملاحظة: تأكدي من اسم العمود في جدول authorities
}
    }
    /**
     * دالة التصعيد اليدوي: تنقل الشكوى للمستوى الإداري الأعلى (تعديل التدرج: من 1 إلى 2 ومن 2 إلى 3).
     */
    public function escalate(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::findOrFail($id);

        if (!$complain->canEscalate()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تصعيد هذه الشكوى حالياً (ربما لم تنتهِ مهلة الـ 5 أيام أو أنها محلولة).',
            ], 422);
        }

        // التدرج التلقائي الصاعد بناءً على التعديل الجديد للـ ليفل
        $nextLevel = match(intval($complain->assigned_level)) {
            1 => 2, // من الموظف لمدير القسم
            2 => 3, // من مدير القسم لمدير الجهة
            default => null,
        };

        if (!$nextLevel) {
            return response()->json(['success' => false, 'message' => 'الشكوى وصلت لأعلى مستوى إداري بالفعل.'], 422);
        }

        $complain->update([
            'level'          => $nextLevel, // تحديث المستوى المطلوب للمعالجة
            'assigned_level' => $nextLevel,
            'assigned_at'    => now(), // تصفير العداد للمسؤول الجديد
            'status'         => Complain::STATUS_PENDING, 
        ]);

        Log::info("Complaint {$complain->complain_number} escalated to level {$nextLevel}");

        return response()->json([
            'success' => true,
            'message' => 'تم تصعيد الشكوى بنجاح للمستوى الإداري الأعلى.',
            'data'    => [
                'new_level'   => $complain->assigned_level,
                'assigned_at' => $complain->assigned_at
            ]
        ]);
    }

    /**
     * عرض تفاصيل الشكوى بالكامل 
     */
    public function getComplaintDetails(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with([
            'user', 'authority', 'department', 'attachments', 'chat.messages.sender'
        ])->findOrFail($id);

        if (!$user->isEmployee() && !$user->isAdmin() && !$user->isDeptManager() && !$user->isAuthorityManager()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'title'           => $complain->title,
                'description'     => $complain->description,
                'status'          => $complain->status,
                'assigned_level'  => $complain->assigned_level,
                'level_name'      => $complain->level_name,
                'can_escalate'    => $complain->canEscalate(),
                'submitted_at'    => $complain->created_at,
                'user'            => [
                    'id'       => $complain->user->id,
                    'name'     => $complain->user->name,
                    'score'    => $complain->user->score,
                    'priority' => $this->calculatePriority($complain->user->score),
                ],
                'chat'            => $this->formatChat($complain),
                'attachments'     => $complain->attachments->map(fn($a) => [
                    'file_path' => asset('storage/' . $a->file_path),
                    'file_type' => $a->file_type,
                ]),
            ],
        ], 200);
    }


    private function calculatePriority($score): string
    {
        if ($score >= 10) return 'High';
        if ($score >= 5) return 'Medium';
        return 'Low';
    }

    private function formatChat($complain)
    {
        if (!$complain->chat) return null;

        return [
            'chat_id'  => $complain->chat->id,
            'is_open'  => $complain->chat->is_open && $complain->status === Complain::STATUS_IN_PROGRESS,
            'messages' => $complain->chat->messages->map(fn($m) => [
                'message' => $m->message,
                'sender'  => $m->sender->name ?? 'النظام',
                'sent_at' => $m->sent_at,
            ]),
        ];
    }

    private function sendStatusEmail($complain, $status)
    {
        if ($complain->user && $complain->user->email) {
            try {
                Mail::to($complain->user->email)->send(new StatusChangedMail(
                    name: $complain->user->name,
                    complainNumber: $complain->complain_number,
                    title: $complain->title,
                    status: $status,
                ));
            } catch (\Exception $e) {
                Log::error("Email failed for {$complain->complain_number}: " . $e->getMessage());
            }
        }
    }
}