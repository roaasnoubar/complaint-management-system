<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NotificationService;

class ComplainObserver
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Triggered when a complaint is first created.
     * Notifies all employees in the target department.
     */
    public function created($complain): void
    {
        // Notify all employees in the assigned department
        $employees = User::query()
            ->where('department_id', $complain->department_id)
            ->where('id', '!=', $complain->user_id)
            ->whereHas('role', fn ($q) => $q->where('name', 'employee'))
            ->get();

        foreach ($employees as $employee) {
            $this->notificationService->complaintAssigned(
                $employee->id,
                $complain->title,
                $complain->department->name ?? 'the department'
            );
        }

        // Notify the submitter that their complaint was submitted
        $this->notificationService->complaintSubmitted(
            $complain->user_id,
            $complain->title
        );
    }

    /**
     * Triggered when complaint fields change (status, assigned_level).
     */
    public function updated($complain): void
    {
        // Status changed
        if ($complain->isDirty('status')) {
            $newStatus = $complain->status;

            // Notify the complaint owner
            $this->notificationService->statusChanged(
                $complain->user_id,
                $complain->title,
                $newStatus
            );

            // If resolved, send a dedicated resolved notification
            if ($newStatus === 'Resolved') {
                $this->notificationService->resolved(
                    $complain->user_id,
                    $complain->title
                );
            }
        }

        // Escalation: assigned_level changed upward
        if ($complain->isDirty('assigned_level')) {
            $level = $complain->assigned_level;
            
            // 1. تحديد اسم المستوى (لإشعار صاحب الشكوى)
            $escalatedToName = match ($level) {
                1       => 'Head of Organization',
                2       => 'Department Manager',
                3       => 'Employee',
                default => 'a higher authority',
            };
        
            // 2. إشعار صاحب الشكوى
            $this->notificationService->escalated(
                $complain->user_id,
                $complain->title,
                $escalatedToName
            );
        
            // 3. جلب مدير الجهة (جامعة الشام - Level 1)
            // نبحث عنه في النظام كاملاً لأنه المسؤول الأعلى
            $headOfOrg = \App\Models\User::whereHas('role', function ($q) {
                $q->where('level', 1);
            })->get();
        
            // 4. جلب مدير القسم (Level 2)
            // نبحث عنه داخل نفس قسم الشكوى حصراً
            $deptManagers = \App\Models\User::where('department_id', $complain->department_id)
                ->whereHas('role', function ($q) {
                    $q->where('level', 2);
                })->get();
        
            // دمج الجميع في قائمة واحدة
            $authorities = $headOfOrg->concat($deptManagers);
        
            \Log::info("Escalation: Notifying " . $authorities->count() . " administrators for Complaint #{$complain->id}");
        
            foreach ($authorities as $authority) {
                $rankName = ($authority->role->level == 1) ? "University Administration" : "Department Manager";
                
                $this->notificationService->complaintAssigned(
                    $authority->id,
                    $complain->title,
                    "Urgent: Complaint escalated to {$rankName}."
                );
            }
        }
    }
}
