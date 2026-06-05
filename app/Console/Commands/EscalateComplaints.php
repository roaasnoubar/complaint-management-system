<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Complain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EscalateComplaints extends Command
{
    
    protected $signature = 'go';

   
    protected $description = 'تصعيد الشكاوى آلياً بين المستويات الإدارية بمجرد مرور الوقت إذا لم تفتح الشكوى';

    public function handle()
    {
        $now = Carbon::now('UTC');
        $delayThreshold = $now->copy()->subMinute();

        $this->info("--- بدء عملية فحص التصعيد التلقائي الزمنية ({$now->toDateTimeString()}) ---");

       
        $toManager = Complain::where('status', 'Pending')
            ->where('assigned_level', 3)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toManager as $complaint) {
            $complaint->update([
                'assigned_level' => 2,
                'assigned_at'    => $now,
                'updated_at'     => $now
            ]);

            $msg = "الشكوى #{$complaint->complain_number} معلقة ولم يفتحها الموظف، تم تصعيدها تلقائياً لمدير القسم.";
            $this->warn($msg);
            Log::info($msg); 
        }

        
        $toAuthority = Complain::where('status', 'Pending')
            ->where('assigned_level', 2)
            ->whereNull('processed_by')
            ->where('assigned_at', '<=', $delayThreshold)
            ->get();

        foreach ($toAuthority as $complaint) {
            $complaint->update([
                'assigned_level' => 1,
                'assigned_at'    => $now,
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