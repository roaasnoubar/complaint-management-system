<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Rating;
use App\Models\Notification; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
class ComplaintController extends Controller
{
    /**
     * تقديم شكوى جديدة
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'title'         => 'required|string',
            'description'   => 'required|string',
            'authority_id'  => 'required|exists:authorities,id',
            'department_id' => 'required|exists:departments,id',
            'full_name'     => 'required|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    
        // 2. إنشاء الشكوى (بدون أي إنشاء للمحادثة)
        $complain = Complain::create([
            'user_id'       => auth()->id(),
            'full_name'     => $request->full_name,
            'authority_id'  => $request->authority_id,
            'department_id' => $request->department_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'priority'      => $request->priority ?? 'normal',
            'status'        => 'Pending',
            'assigned_level' => 3,
            // 'can_chat'   => false, // إذا كان عندك هذا الحقل في الجدول، اجعليه false افتراضياً
        ]);
        // 3. رفع المرفقات
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints/attachments', 'public');
                
                $complain->attachments()->create([
                    'user_id'   => auth()->id(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }
    
        // 4. الرد النهائي (نرسل البيانات بدون محادثة)
        return response()->json([
            'success' => true,
            'message' => 'تم تقديم الشكوى بنجاح برقم: ' . $complain->complain_number,
            'data'    => $complain->load(['attachments', 'department'])
        ], 201);
    }

    /**
     * جلب قائمة الشكاوى الخاصة بالمستخدم المسجل حالياً
     */
    public function index(Request $request): JsonResponse
{
    $user = $request->user();
    $now = \Carbon\Carbon::now();

    // 1. تحديث شامل لقاعدة البيانات (التصعيد التلقائي) قبل جلب البيانات
    // تصعيد لمدير الجهة (1) إذا مر أكثر من دقيقتين
    \DB::table('complains')
        ->where('created_at', '<=', $now->copy()->subMinutes(2))
        ->where('assigned_level', '>', 1)
        ->update(['assigned_level' => 1, 'updated_at' => $now]);

    // تصعيد لمدير القسم (2) إذا مر أكثر من دقيقة (وأقل من دقيقتين)
    \DB::table('complains')
        ->where('created_at', '<=', $now->copy()->subMinutes(1))
        ->where('created_at', '>', $now->copy()->subMinutes(2))
        ->where('assigned_level', '>', 2)
        ->update(['assigned_level' => 2, 'updated_at' => $now]);

    // 2. بناء الاستعلام بناءً على المستويات المحدثة
    $query = Complain::with(['authority:id,name', 'department:id,name', 'attachments', 'user:id,name']);

    if ($user->role?->level === 1) { // مدير الجهة
        $query->where('authority_id', $user->authority_id)
              ->where('assigned_level', 1);
    } 
    elseif ($user->role?->level === 2) { // مدير القسم
        $query->where('department_id', $user->department_id)
              ->where('assigned_level', 2);
    } 
    elseif ($user->role?->level === 3) { // الموظف
        $query->where('department_id', $user->department_id)
              ->where('assigned_level', 3);
    } 
    else {
        $query->where('user_id', $user->id);
    }

    $complaints = $query->orderBy('created_at', 'desc')->get();

    // 3. إضافة الحقول الوهمية للعرض (JSON)
    $complaints->transform(function ($complaint) use ($user, $now) {
        $minutesOld = $complaint->created_at->diffInMinutes($now);
        $complaint->can_chat = false;

        // منطق الشات: مسموح فقط للمستوى الذي تتبع له الشكوى حالياً
        if ($user->role?->level == $complaint->assigned_level) {
            $complaint->can_chat = true;
        }
        // المواطن دائماً يمكنه الشات (أو حسب منطق مشروعك)
        elseif ($user->role?->level === 4 || !$user->role) {
            $complaint->can_chat = true;
        }

        $complaint->current_level_name = match((int)$complaint->assigned_level) {
            1 => 'Authority Manager',
            2 => 'Department Manager',
            3 => 'Employee',
            default => 'Unknown'
        };

        return $complaint;
    });

    return response()->json([
        'success' => true,
        'count'   => $complaints->count(),
        'data'    => $complaints
    ], 200);
}
    /**
     * عرض تفاصيل شكوى واحدة محددة للمستخدم
     */
    public function show(Complain $complain): JsonResponse
{
    $user = auth()->user();

    // السماح إذا كان هو صاحب الشكوى OR إذا كان موظفاً/مديراً (Role level 1, 2, 3)
    $isOwner = $complain->user_id == $user->id;
    $isStaff = in_array($user->role?->level, [1, 2, 3]);

    if (!$isOwner && !$isStaff) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بمشاهدة هذه الشكوى'
        ], 403);
    }

    // هنا السحر: جلب الشكوى مع كل شيء (المواطن، الجهة، القسم، المرفقات، والمحادثة برسائلها)
    return response()->json([
        'success' => true,
        'data'    => $complain->load([
            'user:id,name', 
            'authority', 
            'department', 
            'attachments', 
            'chat.messages.sender' // أضفت sender لتعرفي من أرسل كل رسالة في الشات
        ])
    ]);
}

    /**
     * تقييم المستخدم للجهة بعد حل الشكوى
     */
    public function rateAuthority(Request $request, Complain $complain): JsonResponse
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($complain->status !== 'Resolved') {
            return response()->json([
                'success' => false,
                'message' => 'يمكنك التقييم فقط بعد حل الشكوى (Resolved)'
            ], 400);
        }

        if ($complain->user_id != auth()->id()) {
            return response()->json(['message' => 'غير مصرح لك بتقييم هذه الشكوى'], 403);
        }

        $exists = Rating::where('complain_id', $complain->id)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتقييم هذه الشكوى مسبقاً'
            ], 400);
        }

        Rating::create([
            'user_id'     => auth()->id(),
            'authority_id' => $complain->auth_id,
            'complain_id' => $complain->id,
            'rating'      => $request->rating,
            'comment'     => $request->comment
        ]);

        return response()->json([
            'success' => true,
            'message' => 'شكراً لتقييمك، تم حفظ رأيك بنجاح!'
        ]);
    }

    /**
     * تحديث حالة الشكوى (للموظف/الآدمن) وإرسال إشعار للمواطن
     */
    public function updateStatus(Request $request, $id): JsonResponse
{
    // 1. التحقق من المدخلات (ملاحظات الرفض إجبارية)
    $request->validate([
        'status' => 'required|string|in:Pending,In Progress,Resolved,Rejected',
        'notes'  => 'required_if:status,Rejected|string|max:500' 
    ]);

    $user = $request->user();
    $complain = Complain::with('user')->findOrFail($id);
    $oldStatus = $complain->status;
    $nextStatus = $request->input('status');
    $notes = $request->input('notes');

    //(Security Gate) +
    if (!$user->isAdmin()) {
        if ($complain->department_id != $user->department_id) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، لا تملك صلاحية الوصول لشكاوى هذا القسم.'
            ], 403);
        }
    }

    // 3. التحقق من "منطق الانتقال" (State Machine Logic)
    $allowedNextStatuses = Complain::STATUS_TRANSITIONS[$oldStatus] ?? [];
    if (!in_array($nextStatus, $allowedNextStatuses)) {
        return response()->json([
            'success' => false, 
            'message' => "لا يمكن الانتقال برمجياً من حالة ($oldStatus) إلى حالة ($nextStatus)."
        ], 422);
    }

    // 4. تنفيذ التحديث والعمليات الجانبية
    $complain->status = $nextStatus;

    if ($nextStatus === Complain::STATUS_IN_PROGRESS) {
        $complain->assigned_at = now();
    }

    if ($nextStatus === Complain::STATUS_RESOLVED) {
        $complain->resolved_at = now();
        if ($complain->user) {
            $complain->user->increment('score'); 
        }
    }

    // تخزين الملاحظات (سبب الرفض مثلاً)
    if ($notes) {
        $complain->notes = $notes; 
    }

    $complain->save();

    // 5. إرسال الإشعارات
    $this->sendStatusNotification($complain, $nextStatus, $oldStatus, $notes);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث الحالة بنجاح وإرسال التنبيهات اللازمة.',
        'data' => $complain->load('user')
    ]);
}
/**
 */
private function sendStatusNotification($complain, $nextStatus, $oldStatus, $notes = null) 
{
    if (!$complain->user) return;
    switch ($nextStatus) {
        case 'Resolved':
            $complain->user->sendNotification(
                'بشرى سارة! تم حل شكواك 🎉',
                "تمت معالجة الشكوى رقم ({$complain->complain_number}) بنجاح. شكراً لتعاونك.",
                'RESOLVED'
            );
            break;

        case 'Rejected':
            $complain->user->sendNotification(
                'تم تحديث حالة الشكوى (مرفوضة)',
                "نعتذر منك، تم رفض الشكوى رقم ({$complain->complain_number}). السبب: " . ($notes ?? 'غير محدد'),
                'REJECTED'
            );
            break;

        case 'In Progress':
            if ($oldStatus != 'In Progress') {
                $complain->user->sendNotification(
                    'بدء معالجة الشكوى',
                    "الشكوى رقم ({$complain->complain_number}) قيد المعالجة الآن من قبل القسم المختص.",
                    'STATUS_CHANGED'
                );
            }
            break;
            
        default:
            $complain->user->sendNotification(
                'تحديث حالة الشكوى',
                "تغيرت حالة شكواك رقم ({$complain->complain_number}) إلى {$nextStatus}.",
                'STATUS_CHANGED'
            );
            break;
    }
}
// داخل الملف تأكدي من وجود هذه الدالة:
public function escalateToManager($id) 
{
    // حذفنا حرف الـ t من نهاية الاسم ليتطابق مع ملفك Complain.php
    $complaint = \App\Models\Complain::find($id); 

    if (!$complaint) {
        return response()->json(['error' => 'الشكوى غير موجودة'], 404);
    }

    $complaint->update(['assigned_level' => 2]);

    return response()->json([
        'message' => 'تم التصعيد بنجاح',
        'complaint_id' => $complaint->id
    ]);
}
}