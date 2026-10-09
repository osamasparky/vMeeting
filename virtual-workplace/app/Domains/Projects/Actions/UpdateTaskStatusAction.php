<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * The single entry point for changing a task's status. Every surface — API,
 * hub, bulk edit, the virtual office — goes through here so the approval
 * workflow, milestone completion, goal progress and recurrence stay in step.
 */
class UpdateTaskStatusAction
{
    public function execute(Task $task, string $newStatus): Task
    {
        if (! in_array($newStatus, Task::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$newStatus}");
        }

        $user = Auth::user();
        $wasDone = $task->status === Task::STATUS_DONE;

        if ($newStatus === Task::STATUS_DONE) {
            if ($this->canApprove($task, $user)) {
                // Direct Manager / Admin completion
                $task->update([
                    'status' => Task::STATUS_DONE,
                    'approval_status' => 'approved',
                    'approved_by' => $user?->id,
                    'approved_at' => now(),
                    'completed_at' => now(),
                    'rejection_reason' => null,
                ]);

                // ── Automatically generate next recurring cycle task (once) ──
                if (! $wasDone && $task->isRecurring()) {
                    app(ProcessRecurringTaskAction::class)->execute($task);
                }
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
                    NotificationService::notifyCustom(
                        $pmId,
                        'task_approval_request',
                        __('⏳ Task Approval Needed: ":task"', ['task' => $task->title]),
                        __(':user submitted task ":task" for completion approval.', ['user' => $user?->name ?? 'Team member', 'task' => $task->title]),
                        ['task_id' => $task->id, 'project_id' => $task->project_id],
                        $user?->id
                    );
                }
            }
        } else {
            $leavesApproval = $wasDone || ($task->approval_status === 'pending_approval' && $newStatus !== Task::STATUS_REVIEW);

            $task->update([
                'status' => $newStatus,
                'approval_status' => $leavesApproval ? 'none' : $task->approval_status,
                'completed_at' => null,
            ]);
        }

        $task = $task->fresh(['project', 'assignee', 'reporter', 'approver', 'milestone']);

        // ── Milestone completion and goal progress follow every transition ──
        $task->milestone?->checkAndUpdateStatus();
        if ($task->project) {
            $task->project->goals->each->recalculateProgress();
        }

        return $task;
    }

    /**
     * Who may close a task outright (and approve a member's request): super
     * admins, company admins, team leads (tasks.assign) and the project manager.
     */
    public function canApprove(Task $task, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $membership = OrganizationMember::where('organization_id', $task->organization_id)
            ->where('user_id', $user->id)
            ->first();

        return $user->isSuperAdmin()
            || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')))
            || ($task->project && $task->project->manager_id === $user->id);
    }
}
