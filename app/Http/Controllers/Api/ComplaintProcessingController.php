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
    /**
     * تحديث حالة الشكوى (استلام، حل) مع تحديث نقاط المصداقية وتوقيت التعيين.
     */
    public function acceptAsValid($id): JsonResponse
    {
        $complaint = Complain::findOrFail($id);
        
        // تحديث حالة الشكوى وتوثيق وقت الحل لمنع المشاكل الإحصائية
        $complaint->update([
            'status' => Complain::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        // جلب الطالب صاحب الشكوى عبر العلاقة المحددة في الموديل
        $student = $complaint->user; 
        
        if ($student) {
            // رفع السكور الخاص بالطالب بمقدار 10 نقاط كمكافأة على جديته
            $student->increment('score', 10); 
        }

        // إرسال إشعار عبر الإيميل بالطريقة النظامية
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

        // 2. التحقق من الهرمية (تعديل الإشارة: يمنع لو صلاحية المستخدم أقل من مستوى الشكوى الحالي)
        if (!$user->isAdmin() && intval($user->role->level) < intval($complain->level)) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، هذه الشكوى في مستوى إداري أعلى من صلاحياتك ولا يمكنك معالجتها.'
            ], 403);
        }

        // 3. التحقق من التبعية (للموظف ومدير القسم لضمان بقائهم ضمن قسمهم فقط)
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

        // 5. تنفيذ التعديلات
        $complain->status = $nextStatusValue;
        
        // توثيق رتبة المعالج الحالي (من التوكين)
        $complain->assigned_level = $user->role->level; 

        // إذا تحولت الحالة إلى "قيد المعالجة"
        if ($nextStatusValue === Complain::STATUS_IN_PROGRESS) {
            $complain->assigned_at = now(); 
        }

        // إذا تحولت الحالة إلى "تم الحل" عبر التدفق الطبيعي
        if ($nextStatusValue === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
            if ($complain->user) {
                $complain->user->increment('score', 10); 
            }
        }

        $complain->save();

        // إرسال الإشعار للمستخدم
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
        // 1. استخدمي واجهة الـ DB مباشرة مع استعلام نصي (Raw Query)
        // هذا لا يعتمد على الموديل ولا على الكاش
        $result = \DB::statement("UPDATE complains SET status = 'Rejected', notes = ? WHERE id = ?", [
            $request->rejection_reason ?? 'لا يوجد سبب',
            $id
        ]);
    
        // 2. إذا نجح التحديث سيعود بقيمة true
        if ($result) {
            return response()->json(['message' => 'تم الرفض بنجاح']);
        }
    
        return response()->json(['message' => 'فشل التحديث'], 500);
    }
     /*public function reject(Request $request, $id): JsonResponse
    {
        
        /*
        $user = $request->user(); 
        $complain = Complain::with('user')->findOrFail($id);
    
        // 1. تحديد مستوى الرفض بناءً على دور المستخدم (تعديل الأرقام: الموظف 1، مدير القسم 2، مدير الجهة 3)
        $rejectionLevel = match(true) {
            $user->isEmployee() => 1,         
            $user->isDeptManager() => 2,      
            $user->isAuthorityManager() => 3, 
            $user->isAdmin() => 3,            
            default => null
        };
    
        if ($rejectionLevel === null) {
            return response()->json(['success' => false, 'message' => 'Unauthorized role.'], 403);
        }
    
        // 2. التحقق من التبعية (القسم أو الجهة) لضمان الصلاحية الإقليمية
        if (!$user->isAdmin()) {
            if ($rejectionLevel <= 2) { // تعديل الإشارة لتشمل الموظف ومدير القسم
                if (intval($complain->department_id) !== intval($user->department_id)) {
                    return response()->json(['success' => false, 'message' => 'Not authorized for this department.'], 403);
                }
            } else { 
                if (intval($complain->authority_id) !== intval($user->authority_id)) {
                    return response()->json(['success' => false, 'message' => 'Not authorized for this authority.'], 403);
                }
            }
        }
    
        // 3. التحقق من إدخال سبب الرفض وجوباً
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);
    
        $student = $complain->user;
        $isBanned = false;

        // 4. تنفيذ نظام العقوبات التلقائي (السكور والحظر التلقائي)
        if ($student) {
            // أ. خفض السكور بمقدار 10 نقاط
            $student->decrement('score', 10); 
            
            // ب. زيادة عداد الشكاوى الكاذبة
            $student->increment('false_complaints_count');

            // ج. شرط الطرد والحظر الحاسم (3 شكاوى كاذبة)
            if ($student->false_complaints_count >= 3) {
                $student->update(['is_banned' => true]);
                $student->tokens()->delete(); // طرد فوري وسحب توكنات الفلاتر
                $isBanned = true;
            }
        }
    
        // 5. تحديث الشكوى وحفظ سبب الرفض في حقل الـ notes
        $complain->update([
            'status' => 'Rejected',
            'notes' => $request->rejection_reason,
            'assigned_level' => $rejectionLevel, 
        ]);
    
        // إرسال إيميل بالرفض
        $this->sendStatusEmail($complain, 'Rejected');
    
        if ($isBanned) {
            return response()->json([
                'success' => true,
                'status' => 'banned',
                'message' => "تم رفض الشكوى ككاذبة من قبل {$user->name}. تم حظر الطالب نهائياً من النظام وتدمير الجلسة لتجاوزه 3 شكاوى كاذبة."
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => "تم رفض الشكوى ككاذبة بنجاح من قبل " . $user->name . " وخصم النقاط من الطالب.",
            'data' => [
                'id'             => $complain->id,
                'status'         => $complain->status,
                'assigned_level' => $complain->assigned_level,
                'user_new_score' => $student ? $student->score : null,
                'notes'          => $complain->notes, 
            ]
     
       ], 200);/*
    }
    
    /**
     * دالة التصعيد اليدوي: تنقل الشكوى للمستوى الإداري الأعلى (تعديل التدرج: من 1 إلى 2 ومن 2 إلى 3).
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
            'status'         => Complain::STATUS_PENDING, // تعود كأنها جديدة للمسؤول الأعلى
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
     * عرض تفاصيل الشكوى بالكامل (للموظف والأدمن).
     */
    public function getComplaintDetails(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with([
            'user', 'authority', 'department', 'attachments', 'chat.messages.sender'
        ])->findOrFail($id);

        // الصلاحيات
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