<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Complain;
use Carbon\Carbon;

class CheckEscalation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. استخدام توقيت دمشق للتوافق التام مع الشكاوى الجديدة في قاعدة البيانات
        $now = Carbon::now('Asia/Damascus');
        
        // نطرح دقيقة واحدة وثوانٍ لضمان لقط الشكوى فوراً بمجرد انتهاء وقتها الصارم
        $delay = (clone $now)->subMinute()->addSeconds(5);
    
        // 2. تصعيد تلقائي من مدير القسم (Level 2) إلى مدير الجهة (Level 1)
        // الشروط: معلقة Pending + لم تفتح بعد (processed_by هو NULL) + انتهت دقيقتها بتوقيت دمشق
        Complain::where('status', 'Pending')
            ->where('assigned_level', 2)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 1,
                'assigned_at'    => $now,
                'updated_at'     => $now
            ]);
    
        // 3. تصعيد تلقائي من الموظف (Level 3) إلى مدير القسم (Level 2)
        // الشروط: معلقة Pending + لم تفتح بعد (processed_by هو NULL) + انتهت دقيقتها بتوقيت دمشق
        Complain::where('status', 'Pending')
            ->where('assigned_level', 3)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delay)
            ->update([
                'assigned_level' => 2,
                'assigned_at'    => $now,
                'updated_at'     => $now
            ]);
    
        // تمرير الطلب بشكل آمن وطبيعي إلى الفلوتر بعد تحديث البيانات صامتاً
        return $next($request);
    }
}