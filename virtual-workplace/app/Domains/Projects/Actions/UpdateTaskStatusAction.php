<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UpdateTaskStatusAction
{
    public function execute(Task $task, string $newStatus): Task
    {
        if (!in_array($newStatus, Task::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$newStatus}");
        }

        $user = Auth::user();
        $isManager = false;

        if ($user) {
            $membership = OrganizationMember::where('organization_id', $task->organization_id)
                ->where('user_id', $user->id)
                ->first();

            $isManager = $user->isSuperAdmin()
                || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')))
                || ($task->project && $task->project->manager_id === $user->id);
        }

        if ($newStatus === Task::STATUS_DONE) {
            if ($isManager) {
                // Direct Manager / Admin completion
                $task->update([
                    'status' => Task::STATUS_DONE,
                    'approval_status' => 'approved',
                    'approved_by' => $user?->id,
                    'approved_at' => now(),
                    'completed_at' => now(),
                    'rejection_reason' => null,
                ]);
            } else {
                // Team member requested Done -> needs PM approval
                $task->update([
                    'status' => Task::STATUS_REVIEW,
                    'approval_status' => 'pending_approval',
                    'completed_at' => null,
                ]);

                // Notify Project Manager
                $pmId = $task->project?->manager_id;
                if ($pmId && $pmId !== $user?->id) {
                    \App\Domains\Notifications\Services\NotificationService::notifyCustom(
                        $pmId,
                        'task_approval_request',
                        __("⏳ Task Approval Needed: \":task\"", ['task' => $task->title]),
                        __(":user submitted task \":task\" for completion approval.", ['user' => $user?->name ?? 'Team member', 'task' => $task->title]),
                        ['task_id' => $task->id, 'project_id' => $task->project_id],
                        $user?->id
                    );
                }
            }
        } else {
            $task->update([
                'status' => $newStatus,
                'approval_status' => ($task->approval_status === 'pending_approval' && $newStatus !== Task::STATUS_REVIEW) ? 'none' : $task->approval_status,
                'completed_at' => null,
            ]);
        }

        return $task->fresh(['project', 'assignee', 'reporter', 'approver']);
    }
}
