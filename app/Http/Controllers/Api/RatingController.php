<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    /**
     * تخزين التقييم وتحديث سكور المستخدم
     */
    public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'complain_id'        => 'required|exists:complains,id', 
            'complaint_validity' => 'required|boolean',
            'comment'            => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $complaint = DB::table('complains')->where('id', $validated['complain_id'])->first();
            $userId = $complaint->user_id;

            // --- الخطوة السحرية: حذف التقييم القديم إن وجد لفتح الطريق تلقائياً ---
            DB::table('ratings')
                ->where('complain_id', $validated['complain_id'])
                ->where('user_id', $userId)
                ->delete();

            // الآن نقوم بالإدخال بدون خوف من خطأ الـ Duplicate
            DB::table('ratings')->insert([
                'complain_id'        => $validated['complain_id'],
                'user_id'            => $userId,
                'authority_id'       => auth()->id() ?? 3,
                'complaint_validity' => $validated['complaint_validity'],
                'comment'            => $validated['comment'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // منطق الخصم
            if ($validated['complaint_validity'] == false) {
                $user = DB::table('users')->where('id', $userId)->first();
                
                $newFakeCount = ($user->fake_complaints_count ?? 0) + 1;
                $newScore = max(0, ($user->score ?? 100) - 20);

                DB::table('users')->where('id', $userId)->update([
                    'fake_complaints_count' => $newFakeCount,
                    'score'                 => $newScore,
                    'is_banned'             => ($newFakeCount >= 3)
                ]);

                $message = "تم التقييم بنجاح. رصيد المستخدم الحالي: $newScore";
            } else {
                $message = "تم تسجيل التقييم كشكوى صادقة.";
            }

            return response()->json(['success' => true, 'message' => $message], 201);
        });
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
    public function show(int $id): JsonResponse
    {
        // استخدمي العلاقة 'complaint' كما عرفناها في الموديل
        $rating = Rating::with(['complaint', 'user'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $rating]);
    }
}