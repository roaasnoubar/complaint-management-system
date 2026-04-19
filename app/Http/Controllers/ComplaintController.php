<?php

namespace App\Http\Controllers;

use App\Models\Complain; 
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * تقديم شكوى جديدة - تم إصلاح منطق حفظ المرفقات لملء المصفوفة
     */
    public function submitComplaint(Request $request): \Illuminate\Http\JsonResponse
{
    // --- (بداية الكود التشخيصي) ---
    // إذا لم يجد السيرفر أي ملفات، سيتوقف هنا ويعطيكِ التفاصيل
    if (!$request->hasFile('attachments') && count($request->allFiles()) === 0) {
        return response()->json([
            'error' => 'السيرفر لم يستلم أي ملفات',
            'hint' => 'تأكدي من اختيار File في بوستمان وحذف Content-Type من الهيدرز',
            'request_all' => $request->all(), 
            'files_raw_php' => $_FILES, // هذا سيكشف لنا إذا كان PHP نفسه يرى الملف أم لا
        ], 400);
    }
    // --- (نهاية الكود التشخيصي) ---

    // 1. التحقق من الحقول
    $request->validate([
        'auth_id'       => 'required',
        'department_id' => 'required',
        'title'         => 'required',
        'description'   => 'required',
    ]);

    return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
        
        // 2. إنشاء الشكوى
        $complaint = \App\Models\Complain::create([
            'auth_id'        => $request->auth_id,
            'department_id'  => $request->department_id,
            'title'          => $request->title,
            'description'    => $request->description,
            'user_id'        => auth()->id() ?? 10,
            'priority'       => 'High',
            'status'         => 'Pending',
            'assigned_level' => 3,
            'assigned_at'    => now(),
            'is_valid'       => true,
        ]);

        // 3. رفع الملفات
        $allFiles = $request->allFiles(); 
        foreach ($allFiles as $fileGroup) {
            $files = is_array($fileGroup) ? $fileGroup : [$fileGroup];
            foreach ($files as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $path = $file->store('complaints/' . $complaint->id, 'public');
                    \App\Models\Attachment::create([
                        'complain_id' => $complaint->id,
                        'user_id'     => auth()->id() ?? 10,
                        'file_path'   => $path,
                        'file_type'   => $file->getClientOriginalExtension(),
                    ]);
                }
            }
        }

        $attachmentsFinal = \App\Models\Attachment::where('complain_id', $complaint->id)->get();
        $complaint->setRelation('attachments', $attachmentsFinal);

        return response()->json([
            'success' => true,
            'message' => 'تم الحفظ بنجاح',
            'data' => $complaint,
        ], 201);
    });
}
    /**
     * جلب كافة الشكاوى للموظفين مع المرفقات
     */
    public function getComplaints(Request $request): JsonResponse
    {
        $query = Complain::with(['user', 'authority', 'department', 'attachments']);

        // فلاتر اختيارية
        if ($request->filled('auth_id')) $query->where('auth_id', $request->auth_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);

        $complaints = $query->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low')")
                            ->latest()
                            ->paginate(10);
        
        $complaints->getCollection()->transform(function ($complaint) {
            $complaint->attachments->map(function ($attachment) {
                $attachment->full_url = asset('storage/' . $attachment->file_path);
                return $attachment;
            });
            $complaint->can_escalate = $complaint->canEscalate();
            $complaint->level_name   = $complaint->level_name;
            return $complaint;
        });

        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    /**
     * جلب شكاوى المستخدم الحالي مع المرفقات
     */
    public function getMyComplaints(Request $request): JsonResponse
    {
        $complaints = Complain::with(['authority', 'department', 'attachments'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $complaints->getCollection()->transform(function ($complaint) {
            $complaint->attachments->map(function ($attachment) {
                $attachment->full_url = asset('storage/' . $attachment->file_path);
                return $attachment;
            });
            $complaint->can_escalate = $complaint->canEscalate();
            $complaint->level_name   = $complaint->level_name;
            return $complaint;
        });

        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    /**
     * تحديث حالة الشكوى
     */
    public function updateComplaintStatus(Request $request, $id): JsonResponse
    {
        $complaint = Complain::findOrFail($id);
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complaint->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json(['success' => false, 'message' => 'الشكوى مكتملة بالفعل.'], 422);
        }

        $complaint->status = $allowedNextStatus;
        if ($allowedNextStatus === Complain::STATUS_RESOLVED) {
            $complaint->resolved_at = now();
        }
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة بنجاح.',
            'data'    => $complaint->load('attachments'),
        ], 200);
    }

    /**
     * تصعيد الشكوى
     */
    public function escalateComplaint(Request $request, $id): JsonResponse
    {
        $complaint = Complain::findOrFail($id);

        if ($complaint->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بالعملية.'], 403);
        }

        if (!$complaint->canEscalate()) {
            return response()->json(['success' => false, 'message' => 'لا يمكن التصعيد حالياً.'], 422);
        }

        if ($complaint->assigned_level > 1) {
            $complaint->assigned_level = $complaint->assigned_level - 1;
            $complaint->assigned_at    = now();
            $complaint->save();

            return response()->json([
                'success' => true,
                'message' => 'تم التصعيد بنجاح إلى ' . $complaint->level_name,
                'data'    => $complaint,
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'وصلت الشكوى لأعلى مستوى.'], 422);
    }
}