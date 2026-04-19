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
            $escalatedTo = match ($level) {
                1       => 'Head of Organization',
                2       => 'Department Manager',
                3       => 'Employee',
                default => 'a higher authority',
            };

            $this->notificationService->escalated(
                $complain->user_id,
                $complain->title,
                $escalatedTo
            );
        }
    }
}
