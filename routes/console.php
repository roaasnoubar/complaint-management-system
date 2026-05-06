<?php

use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\EscalateComplaints;

// هذا السطر سيجبر لارافيل على تسجيل الكلاس والأمر go
Artisan::command('go', function () {
    $command = new EscalateComplaints();
    $command->handle();
})->purpose('تصعيد الشكاوى آلياً');