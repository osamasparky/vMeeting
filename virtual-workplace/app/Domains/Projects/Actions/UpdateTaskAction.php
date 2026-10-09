<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;

class UpdateTaskAction
{
    public function __construct(private readonly UpdateTaskStatusAction $updateStatus) {}

    public function execute(Task $task, array $data): Task
    {
        // Status changes go through the workflow (approval, milestones, goals,
        // recurrence) rather than being written straight onto the row.
        $status = $data['status'] ?? null;
        unset($data['status'], $data['completed_at']);

        $oldMilestone = $task->milestone;

        if ($data !== []) {
            $task->update($data);
        }

        if ($status !== null && $status !== $task->status) {
            $this->updateStatus->execute($task, $status);
        } elseif (array_key_exists('milestone_id', $data)) {
            $task->load('milestone');
            $task->milestone?->checkAndUpdateStatus();
        }

        if ($oldMilestone && $oldMilestone->id !== $task->milestone_id) {
            $oldMilestone->checkAndUpdateStatus();
        }

        return $task->fresh(['project', 'assignee', 'reporter', 'phase', 'milestone', 'team', 'parentTask', 'subtasks']);
    }
}
