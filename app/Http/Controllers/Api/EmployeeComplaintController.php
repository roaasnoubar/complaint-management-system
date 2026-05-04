<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeComplaintController extends Controller
{
    /**
     * 1. عرض قائمة الشكاوى (Index)
     */
    public function getComplaints(Request $request): JsonResponse
    {
         // 1. منطق التصعيد (بدلاً من استدعاء الكوماند المفقود)
           $delay = \Carbon\Carbon::now('UTC')->subMinute(); 

           \App\Models\Complain::where('assigned_level', 3)
            ->where('created_at', '<=', $delay)
            ->update([
               'assigned_level' => 2, 
               'updated_at' => now()
    ]);

// 2. تكملة الكود الخاص بكِ
$employee = $request->user();
        if (!$employee->isEmployee() && !$employee->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = Complain::query()
            ->with(['user', 'authority', 'department', 'attachments'])
            ->select('complains.*')
            ->leftJoin('users', 'complains.user_id', '=', 'users.id')
            ->orderBy('users.score', 'desc')
            ->orderBy('complains.created_at', 'asc');

        if ($employee->isEmployee()) {
            $query->where('complains.authority_id', $employee->authority_id)
                  ->where('complains.department_id', $employee->department_id);
        }

        $complaints = $query->paginate(15);

        $complaints->getCollection()->transform(function ($complain) {
            return $this->formatComplainResponse($complain);
        });

        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    /**
     * 2. عرض تفاصيل شكوى محددة (Show)
     */
    public function getComplaint(Request $request, $id): JsonResponse
    {
        $employee = $request->user();

        $complain = Complain::with(['user', 'authority', 'department', 'attachments', 'chat.messages.sender'])
                            ->findOrFail($id);

        if ($employee->isEmployee()) {
            // استخدام != للمقارنة المرنة
            if ($complain->authority_id != $employee->authority_id || 
                $complain->department_id != $employee->department_id) {
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Access Denied. This complaint belongs to another department.'
                ], 403);
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $this->formatComplainResponse($complain, true)
        ], 200);
    }

    /**
     * 3. دالة التنسيق الموحدة
     */
    private function formatComplainResponse($complain, $withFullDetails = false)
    {
        $formatted = [
            'id'               => $complain->id,
            'complain_number'  => $complain->complain_number,
            'title'            => $complain->title,
            'description'      => $complain->description,
            'status'           => $complain->status,
            'assigned_level'   => (int)$complain->assigned_level,
            'submitted_at'     => $complain->created_at->format('Y-m-d H:i'),
            'user'             => [
                'id'    => $complain->user->id,
                'name'  => $complain->user->name,
                'score' => $complain->user->score,
            ],
            'department'       => ['id' => $complain->department->id, 'name' => $complain->department->name],
            'attachments'      => $complain->attachments->map(function ($file) {
                return ['id' => $file->id, 'url' => asset('storage/' . $file->file_path)];
            }),
        ];

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