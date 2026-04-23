<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Complain; // تعديل الاسم هنا
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // تغيير الرد ليتناسب مع API
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * تخزين مرفق جديد لشكوى موجودة
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'complain_id' => 'required|exists:complains,id', // تعديل اسم الجدول والعمود
            'file' => 'required|file|max:20480', // رفعنا الحد لـ 20MB
        ]);

        $file = $request->file('file');
        // تخزين في مجلد Complain
        $path = $file->store('attachments/' . $validated['complain_id'], 'public');

        $attachment = Attachment::create([
            'user_id' => auth()->id() ?? $request->user_id, // دعم الـ API
            'complain_id' => $validated['complain_id'],
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(), // تخزين اللاحقة (pdf, png...)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => $attachment
        ], 201);
    }

    /**
     * حذف مرفق
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.'
        ], 200);
    }
}