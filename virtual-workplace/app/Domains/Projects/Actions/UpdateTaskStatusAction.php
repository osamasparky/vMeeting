<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Services\AutomationEngine;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The single entry point for changing a task's status. Every surface — API,
 * hub, bulk edit, the virtual office — goes through here so the approval
 * workflow, milestone completion, goal progress and recurrence stay in step.
 */
class UpdateTaskStatusAction
{
    /**
     * @param  int|null  $stateId  a project state to land in; ignored when the
     *                             workflow redirects the task (e.g. to review for approval)
     */
    public function execute(Task $task, string $newStatus, ?int $stateId = null): Task
    {
        if (! in_array($newStatus, Task::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$newStatus}");
        }

        // One transaction so automations (run after commit) see the final state.
        return DB::transaction(fn () => $this->transition($task, $newStatus, $stateId));
    }

    private function transition(Task $task, string $newStatus, ?int $stateId): Task
    {
        $user = Auth::user();
        $statusBefore = $task->status;
        $wasDone = $statusBefore === Task::STATUS_DONE;

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

        if ($stateId) {
            $state = ProjectState::find($stateId);
            if ($state && $state->project_id === $task->project_id && ProjectState::groupsMatch($state->group, $task->status)) {
                if ($state->id !== $task->state_id) {
                    $task->forceFill(['state_id' => $state->id])->saveQuietly();
                    AutomationEngine::fire($task, 'state_changed');
                }
            }
        }

        $task = $task->fresh(['project', 'assignee', 'reporter', 'approver', 'milestone', 'state']);

        // ── Followers hear about real status changes ──
        if ($statusBefore !== $task->status) {
            app(NotifyTaskWatchersAction::class)->execute(
                $task,
                'task_status',
                __('🔄 ":task" is now :status', ['task' => $task->title, 'status' => __(Str::headline($task->status))]),
                __(':actor changed the status from :old to :new.', [
                    'actor' => $user?->name ?? __('A team member'),
                    'old' => __(Str::headline($statusBefore)),
                    'new' => __(Str::headline($task->status)),
                ]),
                $user,
            );
        }

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
