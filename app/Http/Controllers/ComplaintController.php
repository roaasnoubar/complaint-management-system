<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
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

        $complaint = Complaint::create([
            'user_id'        => $request->user()->id,
            'auth_id'        => $validated['auth_id'],
            'department_id'  => $validated['department_id'],
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'status'         => Complaint::STATUS_PENDING,
            'assigned_level' => Complaint::LEVEL_EMPLOYEE,
            'assigned_at'    => now(),
            'is_valid'       => true,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path     = $file->storeAs('complaints/' . $complaint->id, $filename, 'public');

                    Attachment::create([
                        'complaint_id' => $complaint->id,
                        'user_id'      => $request->user()->id,
                        'file_path'    => $path,
                        'file_type'    => $file->getClientOriginalExtension(),
                    ]);
                }
            }
        }

        $complaint->load('attachments');

        return response()->json([
            'success' => true,
            'message' => 'Complaint submitted successfully.',
            'data'    => $complaint,
        ], 201);
    }

    public function getComplaints(Request $request): JsonResponse
    {
        $query = Complaint::with(['user', 'authority', 'department', 'attachments']);

        if ($request->has('auth_id')) {
            $query->where('auth_id', $request->auth_id);
        }
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $complaints = $query->latest()->paginate(10);
        
        $complaints->getCollection()->transform(function ($complaint) {
            $complaint->can_escalate = $complaint->canEscalate();
            $complaint->level_name   = $complaint->level_name;
            return $complaint;
        });

        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    public function getMyComplaints(Request $request): JsonResponse
    {
        $complaints = Complaint::with(['authority', 'department', 'attachments'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        $complaints->getCollection()->transform(function ($complaint) {
            $complaint->can_escalate = $complaint->canEscalate();
            $complaint->level_name   = $complaint->level_name;
            return $complaint;
        });

        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    public function updateComplaintStatus(Request $request, $id): JsonResponse
    {
        $complaint         = Complaint::findOrFail($id);
        $allowedNextStatus = Complaint::STATUS_TRANSITIONS[$complaint->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json(['success' => false, 'message' => 'Complaint is already resolved.'], 422);
        }

        $complaint->status = $allowedNextStatus;
        if ($allowedNextStatus === Complaint::STATUS_RESOLVED) {
            $complaint->resolved_at = now();
        }
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $allowedNextStatus,
            'data'    => $complaint,
        ], 200);
    }

    public function escalateComplaint(Request $request, $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);

        if ($complaint->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        if (!$complaint->canEscalate()) {
            $daysLeft = Complaint::ESCALATION_DAYS - now()->diffInDays($complaint->assigned_at ?? $complaint->created_at);
            return response()->json([
                'success' => false,
                'message' => 'Cannot escalate yet. Wait ' . max(0, $daysLeft) . ' day(s).',
            ], 422);
        }

        $complaint->assigned_level = $complaint->assigned_level - 1;
        $complaint->assigned_at    = now();
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Escalated successfully to Level ' . $complaint->assigned_level,
            'data'    => $complaint,
        ], 200);
    }
}