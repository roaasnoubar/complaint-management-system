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
  
    public function store(Request $request)
    {
        if (!auth()->user()->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تفعيل حسابك أولاً لتتمكن من تقديم شكوى.'
            ], 403);
        }
        $request->validate([
            'title'         => 'required|string',
            'description'   => 'required|string',
            'authority_id'  => 'required|exists:authorities,id',
            'department_id' => 'required|exists:departments,id',
            'full_name'     => 'required|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $complaintNumber = now()->format('Ymd') . '-' . rand(1000, 9999);
    
        $complain = Complain::create([
            'complain_number' => $complaintNumber,
            'user_id'       => auth()->id(),
            'full_name'     => $request->full_name,
            'authority_id'  => $request->authority_id,
            'department_id' => $request->department_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'priority'      => $request->priority ?? 'normal',
            'status'        => 'Pending',
            'assigned_level' => 3,
        ]);
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
    
        return response()->json([
            'success' => true,
            'message' => 'تم تقديم الشكوى بنجاح برقم: ' . $complain->complain_number,
            'data'    => $complain->load(['attachments', 'department'])
        ], 201);
    }

    
    public function index(Request $request): JsonResponse
{
    $user = $request->user();
    $now = \Carbon\Carbon::now();

\DB::table('complains')
    ->where('assigned_level', 2) 
    ->where('assigned_at', '<=', $now->copy()->subMinutes(2))
    ->where('status', '!=', 'Resolved')
    ->update([
        'assigned_level' => 1,
        'assigned_at'    => $now, 
        'updated_at'     => $now
    ]);

\DB::table('complains')
    ->where('assigned_level', 3)
    ->where('assigned_at', '<=', $now->copy()->subMinutes(1))
    ->where('status', '!=', 'Resolved')
    ->update([
        'assigned_level' => 2,
        'assigned_at'    => $now, // إعادة تصفير العداد للمستوى الجديد
        'updated_at'     => $now
    ]);
    // 2. بناء الاستعلام بناءً على الصلاحيات
    $query = Complain::with(['authority:id,name', 'department:id,name', 'attachments', 'user:id,name']);

    if ($user->role?->level === 1) { 
        $query->where('authority_id', $user->authority_id)
              ->where('assigned_level', 1);
    } 
    elseif ($user->role?->level === 2) { 
        $query->where('department_id', $user->department_id);
    } 
    elseif ($user->role?->level === 3) { 
        $query->where('department_id', $user->department_id)
              ->where('assigned_level', 3);
    } 
    else { 
        $query->where('user_id', $user->id);
    }

    $complaints = $query->orderBy('created_at', 'desc')->get();

    $complaints->transform(function ($complaint) use ($user, $now) {
        $complaint->can_chat = false;

        // chat logic
        if ($user->role?->level === 2 && $complaint->status !== 'Resolved') {
            $complaint->can_chat = true;
        }
        elseif ($user->role?->level == $complaint->assigned_level) {
            $complaint->can_chat = true;
        }
        elseif ($user->role?->level === 4 || !$user->role) {
            $complaint->can_chat = true;
        }

        $complaint->current_level_name = match((int)$complaint->assigned_level) {
            1 => 'Authority Manager',
            2 => 'Department Manager',
            3 => 'Employee',
            default => 'Unknown'
        };

        $complaint->created_at_human = $complaint->created_at ? $complaint->created_at->diffForHumans() : 'منذ فترة غير محددة';

        return $complaint;
    });

    return response()->json([
        'success' => true,
        'count'   => $complaints->count(),
        'data'    => $complaints
    ], 200);
}

    public function show($id): JsonResponse
{
    $user = auth()->user();

    $complain = \App\Models\Complain::with(['user', 'authority', 'department', 'attachments', 'chat.messages.sender'])
        ->where('id', $id)
        ->orWhere('complain_number', $id) 
        ->first();

    if (!$complain) {
        return response()->json([
            'success' => false,
            'message' => 'الشكوى غير موجودة'
        ], 404);
    }

    // 3. التحقق من الصلاحيات العامة
    $isOwner = $complain->user_id == $user->id;
    $isStaff = in_array($user->role?->level, [0, 1, 2, 3]);

    if (!$isOwner && !$isStaff) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بمشاهدة هذه الشكوى'
        ], 403);
    }

    if (in_array($user->role?->level, [1, 2, 3])) {
        if ($complain->authority_id !== $user->authority_id) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الشكوى تابعة لجهة حكومية أخرى'
            ], 403);
        }

        if ($complain->assigned_level > $user->role->level) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الشكوى مصعدة لمستوى إداري أعلى'
            ], 403);
        }
    }

    $statusMessage = match($complain->status) {
        'Resolved'    => 'شكراً لثقتك في تطبيقنا، تمت معالجة الشكوى بنجاح.',
        'Rejected'    => 'تم الاعتذار عن معالجة الشكوى. السبب: ' . ($complain->admin_reply ?? 'لم يتم ذكر سبب'),
        'In Progress' => 'شكواك قيد المعالجة الآن، نحن نعمل على حلها.',
        'Pending'     => 'تم استلام شكواك وهي بانتظار المراجعة من قبل القسم المختص.',
        default       => 'الشكوى تحت المراجعة.'
    };
    if ($isStaff && $complain->status === 'Pending') {
        $complain->update([
            'status' => 'In Progress',
            'updated_at' => now() 
        ]);
        $complain->status = 'In Progress';
    }
    $statusMessage = match($complain->status) {
        'Resolved'    => 'شكراً لثقتك في تطبيقنا، تمت معالجة الشكوى بنجاح.',
        'Rejected'    => 'تم الاعتذار عن معالجة الشكوى. السبب: ' . ($complain->admin_reply ?? 'لم يتم ذكر سبب'),
        'In Progress' => 'شكواك قيد المعالجة الآن، نحن نعمل على حلها.',
        'Pending'     => 'تم استلام شكواك وهي بانتظار المراجعة من قبل القسم المختص.',
        default       => 'الشكوى تحت المراجعة.'
    };
    // 6. تحميل العلاقات
    $complain->load([
        'user:id,name', 
        'authority', 
        'department', 
        'attachments', 
        'chat.messages.sender'
    ]);

    return response()->json([
        'success' => true,
        'status_message' => $statusMessage, 
        'data'    => $complain
    ]);
}
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
    $request->validate([
        'status' => 'required|string|in:Pending,In Progress,Resolved,Rejected',
        'notes'  => 'required_if:status,Rejected|string|max:500' 
    ]);

    $user = $request->user();
    $complain = Complain::with('user')->findOrFail($id);
    $oldStatus = $complain->status;
    $nextStatus = $request->input('status');
    $notes = $request->input('notes');

    //(Security Gate) 
    if (!$user->isAdmin()) {
        if ($complain->department_id != $user->department_id) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، لا تملك صلاحية الوصول لشكاوى هذا القسم.'
            ], 403);
        }
    }

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

    $this->sendStatusNotification($complain, $nextStatus, $oldStatus, $notes);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث الحالة بنجاح وإرسال التنبيهات اللازمة.',
        'data' => $complain->refresh()->load('user') 
    ]);
}
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
public function escalate(Request $request, $id) 
{
    $complaint = \App\Models\Complain::find($id); 

    if (!$complaint) {
        return response()->json(['error' => 'الشكوى غير موجودة'], 404);
    }

    $user = auth()->user();

    if (!in_array($user->role?->level, [0, 1, 2])) {
        return response()->json([
            'success' => false,
            'message' => 'عذراً، لا تملك صلاحية تصعيد الشكاوى.'
        ], 403);
    }

    $request->validate([
        'target_level' => 'required|in:1,2' 
    ]);

    $targetLevel = $request->target_level;

  
    if ($targetLevel >= $complaint->assigned_level && $user->role?->level != 0) {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن تصعيد الشكوى لمستوى إداري أدنى أو مساوٍ للمستوى الحالي.'
        ], 400);
    }

    // 4. تنفيذ التحديث
    $complaint->update([
        'assigned_level' => $targetLevel,
        'assigned_at'    => now(),
        'status'         => 'Escalated'
    ]);

    $levelName = ($targetLevel == 1) ? 'مدير الجهة' : 'مدير القسم';

    return response()->json([
        'success' => true,
        'message' => "تم تصعيد الشكوى إلى ($levelName) بنجاح",
        'data' => [
            'complaint_id' => $complaint->id,
            'new_level' => $targetLevel
        ]
    ]);
}
public function getComplaintsByStatus(Request $request, $status): JsonResponse
{
    $user = $request->user();


    $validStatuses = ['Pending', 'In Progress', 'Resolved', 'Rejected'];
    if (!in_array($status, $validStatuses)) {
        return response()->json(['success' => false, 'message' => 'حالة غير صالحة'], 400);
    }

    // بناء الاستعلام مع العلاقات الأساسية
    $query = \App\Models\Complain::where('status', $status)
                                 ->with(['user:id,name', 'department', 'authority']);

    // كل شخص يرى فقط ما يخصه
    if (!$user->isAdmin()) {
        $userLevel = $user->role ? (int)$user->role->level : null;

        if ($userLevel === 1) {
            // مدير الجامعة/الجهة (Level 1):رى كل شكاوى الأقسام التابعة لجامعته
            $query->where('authority_id', $user->authority_id);
        } elseif ($userLevel === 2 || $userLevel === 3) {
            // مدير القسم (Level 2) والموظف (Level 3): يريان شكاوى قسمهما المحدد فقط
            $query->where('department_id', $user->department_id);
        } else {
            // المواطن العادي أو الطالب: لا يرى إلا الشكاوى التي قدمها بنفسه
            $query->where('user_id', $user->id);
        }
    }

    $complaints = $query->latest()->get();

    return response()->json([
        'success' => true,
        'status_type' => $status,
        'count' => $complaints->count(),
        'data' => $complaints
    ], 200);
}


function respond(Request $request, $id) 
{
    $complaint = \App\Models\Complain::findOrFail($id);
    $admin = $request->user();

  
    $adminLevel = (int)$admin->role->level; // المستوى من جدول الأدوار
    $complaintLevel = (int)$complaint->assigned_level; // مستوى الشكوى الحالي

    
    if ($adminLevel > $complaintLevel) {
        return response()->json([
            'success' => false,
            'message' => 'عذراً، هذه الشكوى أصبحت من صلاحية الإدارة العليا (مدير الجامعة).'
        ], 403);
    }

    if ($complaintLevel === 1 && $adminLevel > 1) {
        return response()->json([
            'success' => false,
            'message' => 'صلاحياتك لا تسمح بالرد على شكاوى مستوى مدير الجامعة.'
        ], 403);
    }

    $request->validate([
        'reply' => 'required|string|min:5|max:1000',
        'status' => 'required|in:Resolved,Rejected',
        'is_valid' => 'required|boolean'
    ]);

    $complaint->update([
        'status' => $request->status,
        'admin_reply' => $request->reply, 
        'resolved_at' => now(),
        'processed_by' => $admin->id 
    ]);
    
   
    $complaint->load('processor.role');
    $citizen = $complaint->user;
    $citizen->adjustScoreByValidity($request->is_valid);

    $title = ($request->status == 'Resolved') ? 'تمت معالجة شكواك' : 'تحديث بشأن شكواك';
    $citizen->sendNotification($title, $request->reply, 'COMPLAINT_ACTION', ['complaint_id' => $complaint->id]);

    return response()->json([
        'success' => true,
        'message' => 'تم حفظ الرد بنجاح',
        'data' => [
            'resolved_at' => $complaint->resolved_at->format('Y-m-d H:i:s'),
            'handler_info' => [
                'name' => $complaint->processor->name ?? 'غير محدد',
                'role' => $complaint->processor->role->name ?? 'موظف'
            ]
        ]
    ]);
}
/**
 * دالة جلب شكاوى المستخدم المسجل
 */
public function userComplaints(Request $request): JsonResponse
{
    $user = $request->user();
    $complaints = \App\Models\Complain::with(['authority', 'department'])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'count' => $complaints->count(),
        'data' => $complaints
    ], 200);
}
}