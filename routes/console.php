<?php

//use Illuminate\Support\Facades\Schedule;
//use App\Models\Complain;
//use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Console Routes & Task Scheduling
|--------------------------------------------------------------------------
*/

//Schedule::call(function () {
  //  $now = Carbon::now('Asia/Damascus');
    
    //$delay = (clone $now)->subMinute()->addSeconds(5);

    // نبحث عن الشكاوى المعلقة التي لم يتم معالجتها بعد وتجاوزت الوقت المسموح
    //Complain::where('status', 'Pending')
       // ->whereIn('assigned_level', [2, 3])
        //->whereNull('processed_by')
        //->where('assigned_at', '<=', $delay)
        //->chunk(100, function ($complaints) use ($now) {
          //  foreach ($complaints as $complaint) {
                // التصعيد: 2 يصبح 1، و 3 يصبح 2
            //    $newLevel = ($complaint->assigned_level == 2) ? 1 : 2;
                
              //  $complaint->update([
                //    'assigned_level' => $newLevel,
                  //  'assigned_at'    => $now,
                    //'updated_at'     => $now
                //]);
            //}
        //});
//})->everyMinute()->withoutOverlapping(); 
use Illuminate\Support\Facades\Schedule;
use App\Models\Complain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

Schedule::call(function () {
    $now = Carbon::now('Asia/Damascus');
    // نطرح دقيقة لضمان أن الشكوى تجاوزت المهلة
    $delayThreshold = $now->copy()->subMinute();

    // 1. تصعيد الشكاوى من المستوى 3 (موظف) إلى 2 (مدير قسم)
    Complain::where('status', 'Pending')
        ->where('assigned_level', 3)
        ->whereNull('processed_by')
        ->where('assigned_at', '<=', $delayThreshold)
        ->update([
            'assigned_level' => 2,
            'assigned_at'    => $now,
            'updated_at'     => $now
        ]);

    // 2. تصعيد الشكاوى من المستوى 2 (مدير قسم) إلى 1 (مدير جهة)
    Complain::where('status', 'Pending')
        ->where('assigned_level', 2)
        ->whereNull('processed_by')
        ->where('assigned_at', '<=', $delayThreshold)
        ->update([
            'assigned_level' => 1,
            'assigned_at'    => $now,
            'updated_at'     => $now
        ]);

})->everyMinute()->withoutOverlapping();