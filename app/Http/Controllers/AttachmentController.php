<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 * title="My Graduation Project API",
 * version="1.0.0",
 * description="Documentation for Complaints Management System API",
 * @OA\Contact(
 * email="admin@example.com"
 * )
 * )
 * @OA\Server(
 * url="http://127.0.0.1:8000",
 * description="Local Server"
 * )
 */
class AttachmentController extends Controller
{
    /**
     * Store a newly created attachment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'complaint_id' => 'required|exists:complaints,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments/' . $validated['complaint_id'], 'public');

        Attachment::create([
            'user_id' => auth()->id() ?? 1,
            'complaint_id' => $validated['complaint_id'],
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
        ]);

        return redirect()->back()
            ->with('success', __('File uploaded successfully.'));
    }

    /**
     * Remove the specified attachment.
     */
    public function destroy(Attachment $attachment): RedirectResponse
    {
        $complaintId = $attachment->complaint_id;

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return redirect()->route('complaints.show', $complaintId)
            ->with('success', __('Attachment deleted successfully.'));
    }
}
