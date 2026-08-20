<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;
use InvalidArgumentException;

class UpdateTaskStatusAction
{
    public function execute(Task $task, string $newStatus): Task
    {
        if (!in_array($newStatus, Task::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$newStatus}");
        }

        $completedAt = ($newStatus === Task::STATUS_DONE) ? now() : null;

        $task->update([
            'status' => $newStatus,
            'completed_at' => $completedAt,
        ]);

        return $task->fresh(['project', 'assignee', 'reporter']);
    }
}
