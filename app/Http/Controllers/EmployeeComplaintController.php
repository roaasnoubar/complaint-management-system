<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeComplaintController extends Controller
{
    public function getComplaints(Request $request): JsonResponse
    {
        $employee = $request->user();

        if (!$employee->isEmployee() && !$employee->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only employees can access this.',
            ], 403);
        }

        $query = Complain::with(['user', 'authority', 'department', 'attachments']);

        if ($employee->isEmployee()) {
            $query->where('complains.auth_id', $employee->authority_id)
                  ->where('complains.department_id', $employee->department_id);
        }

        if ($request->has('status')) {
            $query->where('complains.status', $request->status);
        }

        if ($request->has('auth_id') && $employee->isAdmin()) {
            $query->where('complains.auth_id', $request->auth_id);
        }

        if ($request->has('department_id') && $employee->isAdmin()) {
            $query->where('complains.department_id', $request->department_id);
        }

        // Sort by user score descending (higher score = higher priority)
        $query->leftJoin('users', 'complains.user_id', '=', 'users.id')
              ->orderBy('users.score', 'desc')
              ->orderBy('complains.created_at', 'asc')
              ->select('complains.*');

        $complains = $query->paginate(10);

        $complains->getCollection()->transform(function ($complain) {
            return [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'title'           => $complain->title,
                'description'     => $complain->description,
                'status'          => $complain->status,
                'is_valid'        => $complain->is_valid,
                'assigned_level'  => $complain->assigned_level,
                'level_name'      => $complain->level_name,
                'can_escalate'    => $complain->canEscalate(),
                'submitted_at'    => $complain->created_at,
                'resolved_at'     => $complain->resolved_at,
                'user'            => [
                    'id'       => $complain->user->id,
                    'name'     => $complain->user->name,
                    'phone'    => $complain->user->phone,
                    'score'    => $complain->user->score,
                    'priority' => $complain->user->score >= 10 ? 'High' : ($complain->user->score >= 5 ? 'Medium' : 'Low'),
                ],
                'authority'   => [
                    'id'   => $complain->authority->id,
                    'name' => $complain->authority->name,
                ],
                'department'  => [
                    'id'   => $complain->department->id,
                    'name' => $complain->department->name,
                ],
                'attachments' => $complain->attachments->map(function ($attachment) {
                    return [
                        'id'        => $attachment->id,
                        'file_path' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                }),
            ];
        });

        return response()->json(['success' => true, 'data' => $complains], 200);
    }

    public function getComplaint(Request $request, $id): JsonResponse
    {
        $employee = $request->user();

        if (!$employee->isEmployee() && !$employee->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $complain = Complain::with(['user', 'authority', 'department', 'attachments', 'chat.messages.sender'])
                            ->findOrFail($id);

        if ($employee->isEmployee()) {
            if ($complain->auth_id !== $employee->authority_id ||
                $complain->department_id !== $employee->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authorized to view this complaint.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'title'           => $complain->title,
                'description'     => $complain->description,
                'status'          => $complain->status,
                'is_valid'        => $complain->is_valid,
                'assigned_level'  => $complain->assigned_level,
                'level_name'      => $complain->level_name,
                'can_escalate'    => $complain->canEscalate(),
                'submitted_at'    => $complain->created_at,
                'resolved_at'     => $complain->resolved_at,
                'user'            => [
                    'id'       => $complain->user->id,
                    'name'     => $complain->user->name,
                    'phone'    => $complain->user->phone,
                    'score'    => $complain->user->score,
                    'priority' => $complain->user->score >= 10 ? 'High' : ($complain->user->score >= 5 ? 'Medium' : 'Low'),
                ],
                'authority'   => [
                    'id'   => $complain->authority->id,
                    'name' => $complain->authority->name,
                ],
                'department'  => [
                    'id'   => $complain->department->id,
                    'name' => $complain->department->name,
                ],
                'attachments' => $complain->attachments->map(function ($attachment) {
                    return [
                        'id'        => $attachment->id,
                        'file_path' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                }),
                'chat'        => $complain->chat ? [
                    'chat_id'  => $complain->chat->id,
                    'is_open'  => $complain->chat->is_open,
                    'messages' => $complain->chat->messages->map(function ($message) {
                        return [
                            'id'        => $message->id,
                            'message'   => $message->message,
                            'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
                            'sent_at'   => $message->sent_at,
                            'sender'    => [
                                'id'   => $message->sender->id,
                                'name' => $message->sender->name,
                            ],
                        ];
                    }),
                ] : null,
            ],
        ], 200);
    }
}