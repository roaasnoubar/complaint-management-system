<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Rating;
use App\Models\Notification; // إضافة موديل الإشعارات
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            'auth_id'       => 'required|exists:authorities,id',
            'department_id' =>'required|exists:departments,id',
            'full_name'     => 'required|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // 2. إنشاء الشكوى
        $complain = Complain::create([
            'user_id'       => auth()->id(),
            'full_name'     => $request->full_name,
            'auth_id'       => $request->auth_id,
            'department_id' => $request->department_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'priority'      => $request->priority ?? 'normal',
            'status'        => 'Pending',
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

        // 4. الرد النهائي
        return response()->json([
            'success' => true,
            'message' => 'تم تقديم الشكوى بنجاح برقم: ' . $complain->complain_number,
            'data'    => $complain->load(['attachments', 'user', 'authority', 'department'])
        ], 201);
    }

    /**
     * جلب قائمة الشكاوى الخاصة بالمستخدم المسجل حالياً
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $complaints = Complain::where('user_id', $user->id)
            ->with(['authority:id,name', 'department:id,name', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();

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
        if ($complain->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بمشاهدة هذه الشكوى'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $complain->load(['user:id,name', 'authority', 'department', 'attachments', 'chat.messages'])
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
    // 1. التحقق من وجود الحالة في الطلب
    $request->validate([
        'status' => 'required|string|in:Pending,In Progress,Resolved,Rejected'
    ]);

    $user = $request->user();
    $complain = Complain::with('user')->findOrFail($id);
    $oldStatus = $complain->status;
    $nextStatus = $request->status;

    // 2. التحقق من الصلاحيات (أدمن أو موظف نفس القسم)
    if (!$user->isAdmin()) {
        if ($complain->auth_id !== $user->authority_id || $complain->department_id !== $user->department_id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل شكاوى هذا القسم.'], 403);
        }
    }

    // 3. التحقق من "منطق الانتقال" المسموح به (من الموديل)
    $allowedNextStatuses = Complain::STATUS_TRANSITIONS[$oldStatus] ?? [];
    if (!in_array($nextStatus, $allowedNextStatuses)) {
        return response()->json([
            'success' => false, 
            'message' => "لا يمكن الانتقال من حالة ($oldStatus) إلى حالة ($nextStatus)."
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
            $complain->user->increment('score'); // زيادة المصداقية
        }
    }

    $complain->save();

    // 5. إرسال الإشعارات (المنطق الذي أضفتيه مؤخراً)
    $this->sendStatusNotification($complain, $nextStatus, $oldStatus);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث الحالة بنجاح وإرسال الإشعار.',
        'data' => $complain
    ]);
}

// دالة مساعدة للإشعارات لجعل الكود أنظف
private function sendStatusNotification($complain, $nextStatus, $oldStatus) {
    if ($nextStatus == 'Resolved') {
        $complain->user->sendNotification(
            'بشرى سارة! تم حل شكواك 🎉',
            "تمت معالجة الشكوى رقم ({$complain->id}) بنجاح.",
            'RESOLVED'
        );
    } elseif ($nextStatus == 'In Progress' && $oldStatus != 'In Progress') {
        $complain->user->sendNotification(
            'تحديث بخصوص شكواك',
            "شكواك رقم ({$complain->id}) قيد المعالجة الآن.",
            'STATUS_CHANGED'
        );
    }
}
}