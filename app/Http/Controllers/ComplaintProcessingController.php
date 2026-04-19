<?php

namespace App\Http\Controllers;

use App\Mail\StatusChangedMail;
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
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with(['user', 'authority', 'department'])->findOrFail($id);

        // 1. التحقق من الصلاحيات
        if (!$user->isEmployee() && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // 2. الموظف يعالج فقط شكاوى قسمه وجهته
        if ($user->isEmployee()) {
            if ($complain->auth_id !== $user->authority_id || $complain->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Not authorized for this department.'], 403);
            }
        }

        // 3. التحقق من الانتقال المسموح به للحالة
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complain->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint is already resolved or in a final state.',
            ], 422);
        }

        $previousStatus   = $complain->status;
        $complain->status = $allowedNextStatus;

        // --- معالجة الثغرات المنطقية ---
        if ($allowedNextStatus === Complain::STATUS_IN_PROGRESS) {
            $complain->assigned_at = now(); // بدء عداد الـ 5 أيام
        }

        if ($allowedNextStatus === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
            if ($complain->user) {
                $complain->user->increment('score'); // زيادة المصداقية
            }
        }

        $complain->save();

        // إرسال الإشعار
        $this->sendStatusEmail($complain, $allowedNextStatus);

        return response()->json([
            'success' => true,
            'message' => "Status updated from {$previousStatus} to {$allowedNextStatus}",
            'data'    => [
                'id'              => $complain->id,
                'current_status'  => $complain->status,
                'user_new_score'  => $complain->user ? $complain->user->score : null,
            ],
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