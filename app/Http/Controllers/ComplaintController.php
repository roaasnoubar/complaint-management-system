
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
        return response()->json([
            'has_file'     => $request->hasFile('attachments'),
            'has_file_0'   => $request->hasFile('attachments.0'),
            'all_files'    => array_keys($request->allFiles()),
            'all_input'    => array_keys($request->all()),
            'content_type' => $request->header('Content-Type'),
        ]);
    }

    public function getComplaints(Request $request): JsonResponse
    {
        $complaints = Complaint::with(['user', 'authority', 'department', 'attachments'])->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    public function getMyComplaints(Request $request): JsonResponse
    {
        $complaints = Complaint::with(['authority', 'department', 'attachments'])
            ->where('user_id', $request->user()->id)->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $complaints], 200);
    }

    public function updateComplaintStatus(Request $request, $id): JsonResponse
    {
        $complaint         = Complaint::findOrFail($id);
        $allowedNextStatus = Complaint::STATUS_TRANSITIONS[$complaint->status] ?? null;
        if (!$allowedNextStatus) {
            return response()->json(['success' => false, 'message' => 'Already resolved.'], 422);
        }
        $complaint->status = $allowedNextStatus;
        if ($allowedNextStatus === Complaint::STATUS_RESOLVED) {
            $complaint->resolved_at = now();
        }
        $complaint->save();
        return response()->json(['success' => true, 'message' => 'Status updated to ' . $allowedNextStatus, 'data' => $complaint], 200);
    }

    public function escalateComplaint(Request $request, $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);
        if ($complaint->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }
        if (!$complaint->canEscalate()) {
            $daysLeft = Complaint::ESCALATION_DAYS - now()->diffInDays($complaint->assigned_at ?? $complaint->created_at);
            return response()->json(['success' => false, 'message' => 'Cannot escalate yet. Wait ' . max(0, $daysLeft) . ' day(s).'], 422);
        }
        $previousLevel             = $complaint->assigned_level;
        $complaint->assigned_level = $complaint->assigned_level - 1;
        $complaint->assigned_at    = now();
        $complaint->save();
        return response()->json(['success' => true, 'message' => 'Escalated from Level ' . $previousLevel . ' to Level ' . $complaint->assigned_level], 200);
    }
}