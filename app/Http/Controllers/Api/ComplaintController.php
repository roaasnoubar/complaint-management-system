<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Rating; // تأكدي من وجود هذا الموديل
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
        'auth_id'       => 'required',
        'department_id' => 'required',
        'full_name'     => 'required|string',
        'attachments'   => 'nullable|array',
        'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    // 2. إنشاء الشكوى (باستخدام الموديل الصحيح Complain)
    // ملاحظة: الموديل يولد الرقم تلقائياً فلا داعي لكتابته هنا
    $complain = \App\Models\Complain::create([
        'user_id'       => auth()->id(),
        'full_name'     => $request->full_name,
        'auth_id'       => $request->auth_id,
        'department_id' => $request->department_id,
        'title'         => $request->title,
        'description'   => $request->description,
        'priority'      => $request->priority ?? 'normal',
        'status'        => 'Pending',
    ]);

    // 3. رفع المرفقات وحل مشكلة Field 'user_id' doesn't have a default value
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('complaints/attachments', 'public');
            
            $complain->attachments()->create([
                'user_id'   => auth()->id(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                // حذفنا سطر file_name لعدم وجود العمود في الجدول
            ]);
        }
    }
    // 4. الرد النهائي مع تحميل البيانات المرتبطة
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
        // التحقق من الملكية لضمان الأمان
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

        // 1. التأكد أن الشكوى مغلقة (Resolved) قبل السماح بالتقييم
        if ($complain->status !== Complain::STATUS_RESOLVED) {
            return response()->json([
                'success' => false,
                'message' => 'يمكنك التقييم فقط بعد حل الشكوى (Resolved)'
            ], 400);
        }

        // 2. التأكد أن المستخدم هو صاحب الشكوى
        if ($complain->user_id != auth()->id()) {
            return response()->json(['message' => 'غير مصرح لك بتقييم هذه الشكوى'], 403);
        }

        // 3. منع التكرار: التأكد أن الشكوى لم تُقيم من قبل
        $exists = Rating::where('complain_id', $complain->id)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتقييم هذه الشكوى مسبقاً'
            ], 400);
        }

        // 4. إنشاء التقييم
        Rating::create([
            'user_id'     => auth()->id(),
            'authority_id'    => $complain->auth_id,
            'complain_id' => $complain->id,
            'rating'      => $request->rating,
            'comment'     => $request->comment
        ]);

        return response()->json([
            'success' => true,
            'message' => 'شكراً لتقييمك، تم حفظ رأيك بنجاح!'
        ]);
    }
}