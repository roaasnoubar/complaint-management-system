<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Complain;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Console Routes & Task Scheduling
|--------------------------------------------------------------------------
|
| هذا الملف مسؤول عن المهام التلقائية (Cron Jobs).
| يتم تنفيذ هذه المهمة في الخلفية كل دقيقة بدقة عالية.
|
*/

Schedule::call(function () {
    // 1. تحديد التوقيت بدقة (بتوقيت دمشق)
    $now = Carbon::now('Asia/Damascus');
    
    // 2. تحديد المهلة الزمنية (دقيقة + 5 ثوانٍ إضافية كمرونة تقنية)
    $delay = (clone $now)->subMinute()->addSeconds(5);

    // 3. تنفيذ عملية التصعيد التلقائي
    // نبحث عن الشكاوى المعلقة التي لم يتم معالجتها بعد وتجاوزت الوقت المسموح
    Complain::where('status', 'Pending')
        ->whereIn('assigned_level', [2, 3])
        ->whereNull('processed_by')
        ->where('assigned_at', '<=', $delay)
        ->chunk(100, function ($complaints) use ($now) {
            foreach ($complaints as $complaint) {
                // التصعيد: 2 يصبح 1، و 3 يصبح 2
                $newLevel = ($complaint->assigned_level == 2) ? 1 : 2;
                
                $complaint->update([
                    'assigned_level' => $newLevel,
                    'assigned_at'    => $now,
                    'updated_at'     => $now
                ]);
            }
        });
})->everyMinute()->withoutOverlapping(); 
