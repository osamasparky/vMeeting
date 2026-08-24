<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;

class AssignTaskAction
{
    public function execute(Task $task, ?string $assigneeId, ?\App\Domains\Identity\Models\User $actor = null): Task
    {
        $oldAssignee = $task->assignee_id;

        $task->update([
            'assignee_id' => $assigneeId,
        ]);

        if ($assigneeId && $assigneeId !== $oldAssignee) {
            \App\Domains\Notifications\Services\NotificationService::notifyTaskAssigned(
                $task,
                $assigneeId,
                $actor ?: \Illuminate\Support\Facades\Auth::user()
            );
        }

        return $task->fresh(['project', 'assignee', 'reporter']);
    }
}
