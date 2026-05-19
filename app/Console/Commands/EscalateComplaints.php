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
    protected $description = 'تصعيد الشكاوى آلياً بين المستويات الإدارية بمجرد مرور الوقت إذا لم تفتح الشكوى';

    public function handle()
    {
        // استخدام UTC لضمان التطابق التام مع قاعدة البيانات ومنع مشاكل فروقات التوقيت
        $now = Carbon::now('UTC');
        $delayThreshold = $now->copy()->subMinute(); // حد التأخير الصارم: دقيقة واحدة

        $this->info("--- بدء عملية فحص التصعيد التلقائي الزمنية ({$now->toDateTimeString()}) ---");

        // 1. التصعيد التلقائي الأول: من الموظف (Level 3) إلى مدير القسم (Level 2)
        // الشروط: الحالة معلقة "Pending" + المستوى الحالي موظف (3) + لم تفتح بعد (processed_by هو NULL) + مرّت دقيقة
        $toManager = Complain::where('status', 'Pending')
            ->where('assigned_level', 3)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toManager as $complaint) {
            $complaint->update([
                'assigned_level' => 2,
                'assigned_at'    => $now, // إعادة ضبط العداد لتبدأ "دقيقة" مدير القسم من هذه اللحظة تلقائياً
                'updated_at'     => $now
            ]);

            $msg = "الشكوى #{$complaint->complain_number} معلقة ولم يفتحها الموظف، تم تصعيدها تلقائياً لمدير القسم.";
            $this->warn($msg);
            Log::info($msg); // توثيق العملية في ملفات الـ Log للسيرفر
        }

        // 2. التصعيد التلقائي الثاني: من مدير القسم (Level 2) إلى مدير الجهة (Level 1)
        // الشروط: الحالة معلقة "Pending" + المستوى الحالي مدير قسم (2) + لم تفتح بعد (processed_by لا يزال NULL) + مرّت دقيقة ثانية
        $toAuthority = Complain::where('status', 'Pending')
            ->where('assigned_level', 2)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toAuthority as $complaint) {
            $complaint->update([
                'assigned_level' => 1,
                'assigned_at'    => $now, // إعادة ضبط العداد لتبدأ "دقيقة" مدير الجهة السيادية
                'updated_at'     => $now
            ]);

            $msg = "الشكوى #{$complaint->complain_number} معلقة ولم يفتحها مدير القسم، تم تصعيدها تلقائياً لمدير الجهة.";
            $this->error($msg);
            Log::info($msg);
        }

        // إذا لم يجد النظام أي شكوى متأخرة تنطبق عليها الشروط
        if ($toManager->isEmpty() && $toAuthority->isEmpty()) {
            $this->line("كل الشكاوى المعلقة ضمن الوقت المسموح، أو تم فتحها وبدء معالجتها بشرّياً.");
        }

        $this->info("--- انتهت عملية فحص التصعيد التلقائي بنجاح ---");
        
        return Command::SUCCESS;
    }
}