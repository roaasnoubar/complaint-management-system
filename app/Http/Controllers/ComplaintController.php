<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    public function submitComplaint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auth_id'       => 'required|exists:authorities,id',
            'department_id' => 'required|exists:departments,id',
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:20480',
        ]);

        $complain = Complain::create([
            'user_id'        => $request->user()->id,
            'auth_id'        => $validated['auth_id'],
            'department_id'  => $validated['department_id'],
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'status'         => Complain::STATUS_PENDING,
            'assigned_level' => Complain::LEVEL_EMPLOYEE,
            'assigned_at'    => now(),
            'is_valid'       => true,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path     = $file->storeAs('complains/' . $complain->id, $filename, 'public');

                    Attachment::create([
                        'complain_id' => $complain->id,
                        'user_id'     => $request->user()->id,
                        'file_path'   => $path,
                        'file_type'   => $file->getClientOriginalExtension(),
                    ]);
                }
            }
        }

        $complain->load('attachments');

        return response()->json([
            'success' => true,
            'message' => 'Complain submitted successfully.',
            'data'    => [
                'id'               => $complain->id,
                'complain_number'  => $complain->complain_number,
                'title'            => $complain->title,
                'description'      => $complain->description,
                'status'           => $complain->status,
                'is_valid'         => $complain->is_valid,
                'assigned_level'   => $complain->assigned_level,
                'level_name'       => $complain->level_name,
                'auth_id'          => $complain->auth_id,
                'department_id'    => $complain->department_id,
                'created_at'       => $complain->created_at,
                'attachments'      => $complain->attachments->map(function ($attachment) {
                    return [
                        'id'        => $attachment->id,
                        'file_path' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                }),
            ],
        ], 201);
    }

    public function getComplaints(Request $request): JsonResponse
    {
        $query = Complain::with(['user', 'authority', 'department', 'attachments']);

        if ($request->has('auth_id')) {
            $query->where('auth_id', $request->auth_id);
        }
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $complains = $query->latest()->paginate(10);
        $complains->getCollection()->transform(function ($complain) {
            $complain->can_escalate = $complain->canEscalate();
            $complain->level_name   = $complain->level_name;
            return $complain;
        });

        return response()->json(['success' => true, 'data' => $complains], 200);
    }

    public function getMyComplaints(Request $request): JsonResponse
    {
        $complains = Complain::with(['authority', 'department', 'attachments'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $complains->getCollection()->transform(function ($complain) {
            $complain->can_escalate = $complain->canEscalate();
            $complain->level_name   = $complain->level_name;
            return $complain;
        });

        return response()->json(['success' => true, 'data' => $complains], 200);
    }

    public function updateComplaintStatus(Request $request, $id): JsonResponse
    {
        $complain          = Complain::findOrFail($id);
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complain->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json(['success' => false, 'message' => 'Complain is already resolved.'], 422);
        }

        $complain->status = $allowedNextStatus;
        if ($allowedNextStatus === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
        }
        $complain->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $allowedNextStatus,
            'data'    => [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'status'          => $complain->status,
                'resolved_at'     => $complain->resolved_at,
            ],
        ], 200);
    }

    public function escalateComplaint(Request $request, $id): JsonResponse
    {
        $complain = Complain::findOrFail($id);

        if ($complain->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        if (!$complain->canEscalate()) {
            $daysLeft = Complain::ESCALATION_DAYS - now()->diffInDays($complain->assigned_at ?? $complain->created_at);
            return response()->json([
                'success' => false,
                'message' => 'Cannot escalate yet. Wait ' . max(0, $daysLeft) . ' more day(s).',
                'data'    => [
                    'complain_number' => $complain->complain_number,
                    'current_level'   => $complain->assigned_level,
                    'level_name'      => $complain->level_name,
                    'days_left'       => max(0, $daysLeft),
                ],
            ], 422);
        }

        $previousLevel           = $complain->assigned_level;
        $complain->assigned_level = $complain->assigned_level - 1;
        $complain->assigned_at    = now();
        $complain->save();

        return response()->json([
            'success' => true,
            'message' => 'Escalated from Level ' . $previousLevel . ' to Level ' . $complain->assigned_level,
            'data'    => [
                'complain_number' => $complain->complain_number,
                'previous_level'  => $previousLevel,
                'current_level'   => $complain->assigned_level,
                'level_name'      => $complain->level_name,
            ],
        ], 200);
    }
}