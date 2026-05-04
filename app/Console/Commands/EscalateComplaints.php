<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Complain;
use Carbon\Carbon;

class EscalateComplaints extends Command
{
    protected $signature = 'go';
    protected $description = 'تصعيد الشكاوى آلياً بناءً على الوقت';

    public function handle()
    {
        // استخدام توقيت UTC لضمان التطابق مع قاعدة البيانات
        $delay = Carbon::now('UTC')->subMinute(); 

        // 1. التصعيد من الموظف (3) إلى مدير القسم (2)
        $toManager = Complain::where('assigned_level', 3)
            ->where('created_at', '<=', $delay)
            ->get();

        foreach ($toManager as $complaint) {
            $complaint->update([
                'assigned_level' => 2,
                'updated_at' => Carbon::now('UTC') // تحديث الوقت لتبدأ دقيقة المدير من الآن
            ]);
            $this->info("تم التصعيد لمدير القسم: الشكوى #{$complaint->id}");
        }

        // 2. التصعيد من مدير القسم (2) إلى مدير الجهة (1)
        $toAuthority = Complain::where('assigned_level', 2)
            ->where('updated_at', '<=', $delay)
            ->get();

        foreach ($toAuthority as $complaint) {
            $complaint->update([
                'assigned_level' => 1,
                'updated_at' => Carbon::now('UTC')
            ]);
            $this->info("تم التصعيد لمدير الجهة: الشكوى #{$complaint->id}");
        }
    }
}