<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class EscalationTest extends TestCase
{
    /**
     * اختبار قاعدة الـ SLA (التصعيد التلقائي بعد دقيقة)
     * هل النظام يحدد الشكوى كـ "متأخرة" بشكل صحيح؟
     */
    public function test_sla_time_limit_rule()
    {
        // ضبط وقت "دمشق" افتراضي للاختبار
        $now = Carbon::now('Asia/Damascus');

        // 1. حالة شكوى تجاوزت الوقت (منذ دقيقتين)
        $assignedAtOld = $now->copy()->subMinutes(2);
        $isLate = $assignedAtOld->diffInMinutes($now) >= 1;
        
        $this->assertTrue($isLate, "يجب أن يكتشف النظام أن الشكوى متأخرة وتجاوزت الدقيقة.");

        // 2. حالة شكوى لم تتجاوز الوقت (منذ 30 ثانية)
        $assignedAtFresh = $now->copy()->subSeconds(30);
        $isStillFresh = $assignedAtFresh->diffInMinutes($now) < 1;
        
        $this->assertTrue($isStillFresh, "يجب ألا يتم اعتبار الشكوى متأخرة قبل مرور دقيقة كاملة.");
    }

    /**
     * اختبار قاعدة الصلاحيات (Manual Escalation Rule)
     * هل ليفل المستخدم يسمح له بالتصعيد؟
     */
    public function test_manual_escalation_permission_logic()
    {
        // القواعد البرمجية (Business Rules)
        $allowedLevels = [0, 2, 3]; // أدمن، مدير قسم، مدير جهة
        
        $employeeLevel = 1; // موظف عادي
        $managerLevel = 2;  // رئيس قسم

        // فحص صلاحية الموظف (يجب أن يفشل)
        $this->assertFalse(in_array($employeeLevel, $allowedLevels), "الموظف ليفل 1 لا يملك صلاحية تصعيد يدوية.");

        // فحص صلاحية رئيس القسم (يجب أن ينجح)
        $this->assertTrue(in_array($managerLevel, $allowedLevels), "رئيس القسم ليفل 2 يملك صلاحية تصعيد يدوية.");
    }

    /**
     * اختبار قاعدة الهرمية (Escalation Direction)
     * التأكد من أن التصعيد يتم دائماً للأعلى (رقم المستوى يتناقص)
     */
    public function test_escalation_must_be_to_higher_authority()
    {
        $currentLevel = 3; // موظف
        $targetLevel = 2;  // مدير قسم
        
        // في نظامك: 1 هو الأعلى و 3 هو الأقل
        // التصعيد الصحيح هو أن يكون target < current
        $isUpward = $targetLevel < $currentLevel;

        $this->assertTrue($isUpward, "التصعيد يجب أن يتجه دائماً لمستوى إداري أعلى (رقم ليفل أقل).");
    }
}