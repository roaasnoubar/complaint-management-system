<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NotificationService;

class ComplainObserver
{
    public function __construct(private NotificationService $notificationService) {}

    
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

        $this->notificationService->complaintSubmitted(
            $complain->user_id,
            $complain->title
        );
    }

    public function updated($complain): void
    {
        if ($complain->isDirty('status')) {
            $newStatus = $complain->status;

            $this->notificationService->statusChanged(
                $complain->user_id,
                $complain->title,
                $newStatus
            );

            if ($newStatus === 'Resolved') {
                $this->notificationService->resolved(
                    $complain->user_id,
                    $complain->title
                );
            }
        }

        if ($complain->isDirty('assigned_level')) {
            $level = $complain->assigned_level;
            
            // 1. تحديد اسم المستوى
            $escalatedToName = match ($level) {
                1       => 'Head of Organization',
                2       => 'Department Manager',
                3       => 'Employee',
                default => 'a higher authority',
            };
        
            $this->notificationService->escalated(
                $complain->user_id,
                $complain->title,
                $escalatedToName
            );
        
            
            $headOfOrg = \App\Models\User::whereHas('role', function ($q) {
                $q->where('level', 1);
            })->get();
        
           
            $deptManagers = \App\Models\User::where('department_id', $complain->department_id)
                ->whereHas('role', function ($q) {
                    $q->where('level', 2);
                })->get();
        
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
