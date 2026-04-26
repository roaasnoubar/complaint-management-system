<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeComplaintController extends Controller
{
    /**
     * عرض قائمة الشكاوى الموجهة لقسم الموظف حصراً
     * يتم الترتيب حسب سكور المستخدم (الأولوية للأكثر مصداقية)
     */
    public function getComplaints(Request $request): JsonResponse
    {
        $employee = $request->user();

        // 1. التحقق من الصلاحيات (يجب أن يكون موظف أو أدمن)
        if (!$employee->isEmployee() && !$employee->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only employees or admins can access this.',
            ], 403);
        }

        // 2. بناء الاستعلام مع العلاقات الأساسية
        $query = Complain::with(['user', 'authority', 'department', 'attachments']);

        // 3. تطبيق فلترة الخصوصية (الموظف يرى قسمه وجهته فقط)
        if ($employee->isEmployee()) {
            $query->where('complains.auth_id', $employee->authority_id)
                  ->where('complains.department_id', $employee->department_id);
        }

        // 4. فلاتر إضافية اختيارية (بناءً على طلب الـ API)
        if ($request->has('status')) {
            $query->where('complains.status', $request->status);
        }

        // الأدمن يمكنه الفلترة يدوياً لأي قسم أو جهة
        if ($employee->isAdmin()) {
            if ($request->has('auth_id')) {
                $query->where('complains.auth_id', $request->auth_id);
            }
            if ($request->has('department_id')) {
                $query->where('complains.department_id', $request->department_id);
            }
        }

        // 5. الترتيب الذكي (Join مع جدول المستخدمين للترتيب حسب السكور)
        $query->leftJoin('users', 'complains.user_id', '=', 'users.id')
              ->orderBy('users.score', 'desc') // الأولوية للسكور الأعلى
              ->orderBy('complains.created_at', 'asc') // ثم الأقدم فالأحدث
              ->select('complains.*');

        $complaints = $query->paginate(15);

        // 6. تحويل البيانات لشكل متناسق مع تطبيق الموبايل
        $complaints->getCollection()->transform(function ($complain) {
            return $this->formatComplainResponse($complain);
        });

        return response()->json([
            'success' => true,
            'data' => $complaints
        ], 200);
    }

    /**
     * عرض تفاصيل شكوى محددة مع المحادثة
     */
    public function getComplaint(Request $request, $id): JsonResponse
    {
        $employee = $request->user();

        $complain = Complain::with(['user', 'authority', 'department', 'attachments', 'chat.messages.sender'])
                            ->findOrFail($id);

        // حماية البيانات: التأكد أن الموظف يحاول عرض شكوى تابعة لقسمه فعلاً
        if ($employee->isEmployee()) {
            if ($complain->auth_id !== $employee->authority_id || 
                $complain->department_id !== $employee->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied. This complaint belongs to another department.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatComplainResponse($complain, true)
        ], 200);
    }

    /**
     * دالة موحدة لتنسيق رد الـ JSON (تمنع تكرار الكود وتسهل التعديل)
     */
    private function formatComplainResponse($complain, $withFullDetails = false)
    {
        $formatted = [
            'id'              => $complain->id,
            'complain_number' => $complain->complain_number,
            'title'           => $complain->title,
            'description'     => $complain->description,
            'status'          => $complain->status,
            'submitted_at'    => $complain->created_at->format('Y-m-d H:i'),
            'resolved_at'     => $complain->resolved_at ? $complain->resolved_at->format('Y-m-d H:i') : null,
            'user'            => [
                'id'       => $complain->user->id,
                'name'     => $complain->user->name,
                'phone'    => $complain->user->phone,
                'score'    => $complain->user->score,
                'priority' => $complain->user->score >= 10 ? 'High' : ($complain->user->score >= 5 ? 'Medium' : 'Low'),
            ],
            'authority'       => ['id' => $complain->authority->id, 'name' => $complain->authority->name],
            'department'      => ['id' => $complain->department->id, 'name' => $complain->department->name],
            'attachments'     => $complain->attachments->map(function ($file) {
                return [
                    'id'        => $file->id,
                    'url'       => asset('storage/' . $file->file_path),
                    'file_type' => $file->file_type
                ];
            }),
        ];

        // إضافة المحادثة فقط في حال عرض الشكوى المفردة
        if ($withFullDetails && $complain->chat) {
            $formatted['chat'] = [
                'id'       => $complain->chat->id,
                'messages' => $complain->chat->messages->map(function ($msg) {
                    return [
                        'message' => $msg->message,
                        'sender'  => $msg->sender->name,
                        'time'    => $msg->created_at->diffForHumans(),
                    ];
                }),
            ];
        }

        return $formatted;
    }
}