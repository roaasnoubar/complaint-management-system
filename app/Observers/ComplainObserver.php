<?php

namespace App\Observers;

use App\Models\Complain;
use App\Models\User;
use App\Services\NotificationService;

class ComplainObserver
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Triggered when a complaint is first created.
     * Notifies all employees in the target department.
     */
    public function created(Complain $complain): void
    {
        // Notify all employees in the assigned department
        $employees = User::where('department_id', $complain->department_id)
            ->where('user_id', '!=', $complain->user_id)
            ->get();

        foreach ($employees as $employee) {
            $this->notificationService->complaintSubmitted(
                $employee->user_id,
                $complain->title
            );
        }

        // Notify the submitter that their complaint was received
        $this->notificationService->complaintAssigned(
            $complain->user_id,
            $complain->title,
            $complain->department->name ?? 'the department'
        );
    }

    /**
     * Triggered when complaint fields change (status, assigned_level).
     */
    public function updated(Complain $complain): void
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
                2       => 'Department Head',
                3       => 'Authority Manager',
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
