<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;

class AssignTaskAction
{
    public function execute(Task $task, ?string $assigneeId): Task
    {
        $task->update([
            'assignee_id' => $assigneeId,
        ]);

        return $task->fresh(['project', 'assignee', 'reporter']);
    }
}
