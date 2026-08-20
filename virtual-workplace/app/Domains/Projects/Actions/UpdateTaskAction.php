<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        if (isset($data['status']) && $data['status'] === Task::STATUS_DONE && $task->status !== Task::STATUS_DONE) {
            $data['completed_at'] = now();
        } elseif (isset($data['status']) && $data['status'] !== Task::STATUS_DONE) {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return $task->fresh(['project', 'assignee', 'reporter', 'phase', 'milestone', 'team', 'parentTask', 'subtasks']);
    }
}
