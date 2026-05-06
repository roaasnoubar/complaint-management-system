<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Complain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EscalateComplaints extends Command
{
    /**
     * اسم الأمر الذي ستنفذينه في التيرمنال
     */
    protected $signature = 'go';

    /**
     * وصف الأمر
     */
    protected $description = 'تصعيد الشكاوى آلياً بين المستويات الإدارية بعد مرور دقيقة واحدة من التأخير';

    public function handle()
    {
        // استخدام UTC لضمان التطابق مع قاعدة البيانات ومنع مشاكل التوقيت المحلي
        $now = Carbon::now('UTC');
        $delayThreshold = $now->copy()->subMinute(); // حد التأخير: دقيقة واحدة

        $this->info("--- بدء عملية فحص التصعيد التلقائي ({$now->toDateTimeString()}) ---");

        // 1. التصعيد من الموظف (Level 3) إلى مدير القسم (Level 2)
        // الشروط: الحالة "قيد المعالجة" + المستوى الحالي موظف + مرّت دقيقة على تاريخ الإسناد
        $toManager = Complain::where('status', Complain::STATUS_IN_PROGRESS)
            ->where('assigned_level', 3)
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toManager as $complaint) {
            $complaint->update([
                'assigned_level' => 2,
                'assigned_at'    => $now, // إعادة ضبط العداد لتبدأ "دقيقة" المدير من الآن
                'updated_at'     => $now
            ]);

            $msg = "الشكوى #{$complaint->complain_number} تم تصعيدها من الموظف إلى مدير القسم.";
            $this->warn($msg);
            Log::info($msg); // تسجيل في ملف الـ Log للتوثيق
        }

        // 2. التصعيد من مدير القسم (Level 2) إلى مدير الجهة (Level 1)
        // الشروط: الحالة "قيد المعالجة" + المستوى الحالي مدير قسم + مرّت دقيقة على تاريخ استلامه للشكوى
        $toAuthority = Complain::where('status', Complain::STATUS_IN_PROGRESS)
            ->where('assigned_level', 2)
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toAuthority as $complaint) {
            $complaint->update([
                'assigned_level' => 1,
                'assigned_at'    => $now, // إعادة ضبط العداد لتبدأ "دقيقة" مدير الجهة من الآن
                'updated_at'     => $now
            ]);

            $msg = "الشكوى #{$complaint->complain_number} تم تصعيدها من مدير القسم إلى مدير الجهة.";
            $this->error($msg);
            Log::info($msg);
        }

        if ($toManager->isEmpty() && $toAuthority->isEmpty()) {
            $this->line("لا توجد شكاوى متأخرة حالياً.");
        }

        $this->info("--- انتهت عملية فحص التصعيد بنجاح ---");
        
        return Command::SUCCESS;
    }
}