<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Complain;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Console Routes & Task Scheduling
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    $now = Carbon::now('Asia/Damascus');
    
    $delay = (clone $now)->subMinute()->addSeconds(5);

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
