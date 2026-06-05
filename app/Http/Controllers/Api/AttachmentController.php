<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Complain; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * storage
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'complain_id' => 'required|exists:complains,id', 
            'file' => 'required|file|max:20480', 
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments/' . $validated['complain_id'], 'public');

        $attachment = Attachment::create([
            'user_id' => auth()->id() ?? $request->user_id, 
            'complain_id' => $validated['complain_id'],
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(), //(pdf, png...)
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
        // 1. التحقق من الصلاحيات: هل المستخدم هو صاحب الملف أو مدير؟
        // نفترض أن لديك دالة isAdmin() داخل الـ User Model
        if ($attachment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الملف.'
            ], 403);
        }
    
        // 2. التحقق من وجود الملف قبل محاولة حذفه
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
    
        // 3. حذف السجل من قاعدة البيانات
        $attachment->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'تم حذف المرفق بنجاح.'
        ], 200);
    }
}