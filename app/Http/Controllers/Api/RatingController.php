<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating; // تم التوحيد بحرف t واحد ليطابق الموديل تماماً
use App\Models\Complain;
use App\Models\Authority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    /**
     * إرسال تقييم الطالب للجهة بعد حل الشكوى (متوافق مع حقول جدول ratings وتطبيق الفلاتر)
     */
   /**
     * إرسال تقييم الطالب للجهة بعد حل الشكوى (نسخة متوافقة بدون تعديل حقول جدول authorities)
     */
    public function submitRating(Request $request, $complainId): JsonResponse    {
        // 1. جلب الشكوى للتأكد من وجودها في قاعدة البيانات
        $complain = Complain::findOrFail($complainId);

        // 2. درع أمان: التأكد أن الطالب الذي يقيم هو نفسه صاحب الشكوى الموثق بالسيرفر
        if (intval($complain->user_id) !== intval($request->user()->id)) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، لا تملك الصلاحية البرمجية لتقييم هذه الشكوى.'
            ], 403);
        }

        // 3. شرط منطقي: لا يمكن التقييم إلا إذا كانت الشكوى مغلقة ومحلولة بنجاح
        if ($complain->status !== Complain::STATUS_RESOLVED) {
            return response()->json([
                'success' => false,
                'message' => 'يمكنك فقط تقييم الشكاوى بعد أن يتم حلها وإغلاقها بنجاح.',
                'data'    => ['current_status' => $complain->status],
            ], 422);
        }

        // 4. منع التكرار العشوائي: التأكد أن الطالب لم يقم بتقييم هذه الشكوى مسبقاً
        $existingRating = Rating::where('complain_id', $complainId)
                                 ->where('user_id', $request->user()->id)
                                 ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتقييم هذه الشكوى سابقاً، لا يمكن تكرار العملية.',
                'data'    => $existingRating,
            ], 422);
        }

        // 5. التحقق من صحة المدخلات القادمة من تطبيق الموبايل (النجوم والتعليق)
        $request->validate([
            'response_speed_score' => 'required|integer|min:1|max:5',
            'comment'              => 'nullable|string|max:500',
        ]);

        // استخدام الـ Database Transaction لضمان الحفظ المترابط والأمان العالي
        return DB::transaction(function () use ($request, $complain, $complainId) {
        
            $authorityId = $complain->authority_id ?? $complain->auth_id;
    
            // 3. الحفظ
            $rating = Rating::create([
                'complain_id'          => $complainId, 
                'user_id'              => $request->user()->id,
                'auth_id'              => $authorityId,
                'rating'               => $request->response_speed_score, // هنا التعديل! ربطنا القيمة بالعمود
                'response_speed_score' => $request->response_speed_score,
                'comment'              => $request->comment,
            ]);
            \Log::info("Saved Rating ID: " . $rating->id . " for Complain ID: " . $rating->complain_id);

            // جلب الجهة لحساب المعدلات ديناميكياً بدون التحديث المباشر للجدول
            $authority = Authority::find($authorityId);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل تقييمك لسرعة استجابة الموظفين بنجاح. شكراً لك.',
                'data'    => [
                    'rating'    => $rating,
                    'authority' => $authority ? [
                        'id'             => $authority->id,
                        'name'           => $authority->name,
                        'average_rating' => $authority->average_rating, // سيحسبها لارافيل تلقائياً
                        'total_ratings'  => $authority->total_ratings,  // سيحسبها لارافيل تلقائياً
                    ] : null,
                ],
            ], 201);
        });
    }

    /**
     * جلب إحصائيات تقييمات جهة معينة وتوزيع النجوم (5 نجوم، 4 نجوم...) للـ Dashboard والويب
     */
    public function getAuthorityRatings($authorityId): JsonResponse
    {
        $authority = Authority::findOrFail($authorityId);

        // جلب التقييمات مرتبة من الأحدث للأقدم مع بيانات الطالب والشكوى المرتبطة بها (Pagination)
        $ratings = Rating::with(['user', 'complain'])
        ->where('auth_id', $authorityId)
                           ->latest()
                           ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => [
                'authority' => [
                    'id'              => $authority->id,
                    'name'            => $authority->name,
                    'average_rating'  => $authority->average_rating ?? 0.0,
                    'total_ratings'   => $authority->total_ratings ?? 0,
                    'score_breakdown' => [
                        '5_stars' => Rating::where('auth_id', $authorityId)->where('response_speed_score', 5)->count(),
                        '4_stars' => Rating::where('auth_id', $authorityId)->where('response_speed_score', 4)->count(),
                        '3_stars' => Rating::where('auth_id', $authorityId)->where('response_speed_score', 3)->count(),
                        '2_stars' => Rating::where('auth_id', $authorityId)->where('response_speed_score', 2)->count(),
                        '1_star'  => Rating::where('auth_id', $authorityId)->where('response_speed_score', 1)->count(),
                    ],
                ],
                'ratings' => $ratings,
            ],
        ], 200);
    }
}