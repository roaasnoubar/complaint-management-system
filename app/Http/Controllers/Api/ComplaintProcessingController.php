<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\StatusChangedMail;
use App\Http\Resources\Api\ComplainResource;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class ComplaintProcessingController extends \App\Http\Controllers\Controller
{
    /**
     * تحديث حالة الشكوى (استلام، حل) مع تحديث نقاط المصداقية وتوقيت التعيين.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with(['user', 'authority', 'department'])->findOrFail($id);
    
        // 1. التحقق من الصلاحيات
        if (!$user->isEmployee() && !$user->isAdmin() && !$user->isDeptManager() && !$user->isAuthorityManager()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access.'], 403);
        }
    
        // 2. التحقق من التبعية (للموظف ومدير القسم)
        if ($user->isEmployee() || $user->isDeptManager()) {
            if (intval($complain->department_id) !== intval($user->department_id)) {
                return response()->json(['success' => false, 'message' => 'هذه الشكوى لا تتبع لقسمك.'], 403);
            }
        }
    
        // 3. التحقق من إمكانية تغيير الحالة
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complain->status] ?? null;
    
        if (!$allowedNextStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint is already resolved or in a final state.',
            ], 422);
        }
    
        $nextStatusValue = is_array($allowedNextStatus) ? $allowedNextStatus[0] : $allowedNextStatus;
        $previousStatus   = $complain->status;
    
        // --- التعديلات الجوهرية هنا ---
        
        $complain->status = $nextStatusValue;
        
        // توثيق رتبة المعالج الحالي (إذا دخلتِ بتوكين مدير القسم سيصبح هنا 2)
        $complain->assigned_level = $user->role->level; 
    
        if ($nextStatusValue === Complain::STATUS_IN_PROGRESS) {
            $complain->assigned_at = now(); 
        }
    
        if ($nextStatusValue === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
            if ($complain->user) {
                $complain->user->increment('score'); 
            }
        }
    
        // حفظ كل التغييرات في قاعدة البيانات
        $complain->save();
    
        // إرسال الإشعار (نمرر القيمة النصية وليس المصفوفة)
        $this->sendStatusEmail($complain, $nextStatusValue);
    
        // الرد النهائي الموحد (تم حذف الـ return الزائد الذي كان قبله)
        return response()->json([
            'success' => true,
            'message' => "Status updated from {$previousStatus} to {$nextStatusValue}",
            'data'    => [
                'id'             => $complain->id,
                'current_status' => $complain->status,
                'assigned_level' => $complain->assigned_level, 
                'level_name'     => $complain->level_name,     
                'user_new_score' => $complain->user ? $complain->user->score : null,
            ],
        ], 200);
    }
    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user(); // جلب بيانات المستخدم من التوكين الحالي
        $complain = Complain::with('user')->findOrFail($id);
    
        // 1. تحديد مستوى الرفض والاسم بناءً على دور المستخدم (Role) من التوكين
        // نفترض أن الأدوار هي: employee, dept_manager, auth_manager
        $rejectionLevel = match(true) {
            $user->isEmployee() => 3,         // موظف
            $user->isDeptManager() => 2,      // مدير قسم
            $user->isAuthorityManager() => 1, // مدير جهة
            $user->isAdmin() => 1,            // الأدمن يعامل كأعلى مستوى
            default => null
        };
    
        if ($rejectionLevel === null) {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }
    
        // 2. التحقق من التبعية (القسم أو الجهة) لضمان عدم رفض شكوى غريبة
        if (!$user->isAdmin()) {
            if ($rejectionLevel >= 2) { // موظف أو مدير قسم
                if (intval($complain->department_id) !== intval($user->department_id)) {
                    return response()->json(['success' => false, 'message' => 'Not authorized for this department.'], 403);
                }
            } else { // مدير جهة
                if (intval($complain->authority_id) !== intval($user->authority_id)) {
                    return response()->json(['success' => false, 'message' => 'Not authorized for this authority.'], 403);
                }
            }
        }
    
        // 3. التحقق من إدخال السبب
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);
    
        // 4. تنفيذ الرفض وخصم النقاط
        // خصم نقطة واحدة من سكور اليوزر (أو القيمة التي تفضلينها)
        if ($complain->user) {
            $complain->user->decrement('score', 1); 
        }
    
        $complain->update([
            'status' => 'Rejected',
            'notes' => $request->rejection_reason,
            'assigned_level' => $rejectionLevel, // تخزين المستوى الذي قام بالرفض بناءً على التوكين
        ]);
    
        // 5. إرسال إشعار
        $this->sendStatusEmail($complain, 'Rejected');
    
        return response()->json([
            'success' => true,
            'message' => "تم رفض الشكوى بنجاح من قبل " . $user->name,
            'data' => [
                'id' => $complain->id,
                'status' => $complain->status,
                'assigned_level' => $complain->assigned_level,
                'level_name' => $complain->level_name, // سيعطي الاسم بناءً على الـ assigned_level الجديد
                'user_new_score' => $complain->user ? $complain->user->score : null
            ]
        ], 200);
    }
    /**
     * دالة التصعيد اليدوي: تنقل الشكوى للمستوى الإداري الأعلى بعد مرور المدة المحددة.
     */
    public function escalate(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::findOrFail($id);

        // التأكد من أحقية التصعيد زمنياً ومنطقياً
        if (!$complain->canEscalate()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تصعيد هذه الشكوى حالياً (ربما لم تنتهِ مهلة الـ 5 أيام أو أنها محلولة).',
            ], 422);
        }

        $nextLevel = match($complain->assigned_level) {
            Complain::LEVEL_EMPLOYEE => Complain::LEVEL_MANAGER,
            Complain::LEVEL_MANAGER  => Complain::LEVEL_HEAD,
            default                  => null,
        };

        if (!$nextLevel) {
            return response()->json(['success' => false, 'message' => 'الشكوى وصلت لأعلى مستوى إداري بالفعل.'], 422);
        }

        $complain->update([
            'assigned_level' => $nextLevel,
            'assigned_at'    => now(), // تصفير العداد للمدير الجديد
            'status'         => Complain::STATUS_PENDING, // تعود كأنها جديدة للمسؤول الأعلى
        ]);

        Log::info("Complaint {$complain->complain_number} escalated to level {$nextLevel}");

        return response()->json([
            'success' => true,
            'message' => 'تم تصعيد الشكوى بنجاح للمستوى الإداري الأعلى.',
            'data'    => [
                'new_level_name' => $complain->level_name,
                'assigned_at'    => $complain->assigned_at
            ]
        ]);
    }

    /**
     * عرض تفاصيل الشكوى بالكامل (للموظف والأدمن).
     */
    public function getComplaintDetails(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with([
            'user', 'authority', 'department', 'attachments', 'chat.messages.sender'
        ])->findOrFail($id);

        // الصلاحيات
        if (!$user->isEmployee() && !$user->isAdmin()) {
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

    // --- دالات مساعدة (Private Helpers) لضمان نظافة الكود ---

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
                'sender'  => $m->sender->name,
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