<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use Illuminate\Support\Facades\Auth;

class AssignTaskAction
{
    public function execute(Task $task, ?string $assigneeId, ?User $actor = null): Task
    {
        $oldAssignee = $task->assignee_id;

        $task->update([
            'assignee_id' => $assigneeId,
        ]);

        if ($assigneeId && $assigneeId !== $oldAssignee) {
            NotificationService::notifyTaskAssigned(
                $task,
                $assigneeId,
                $actor ?: Auth::user()
            );
        }

        return $task->fresh(['project', 'assignee', 'reporter']);
    }
}
