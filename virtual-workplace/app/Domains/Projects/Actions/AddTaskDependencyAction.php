<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskDependency;
use App\Domains\Tenancy\Models\Organization;
use InvalidArgumentException;

class AddTaskDependencyAction
{
    /**
     * Add a dependency ensuring no circular dependency is introduced.
     * $task depends on $dependsOnTask (Predecessor: dependsOnTask -> Successor: task).
     */
    public function execute(
        Organization $organization,
        Task $task,
        Task $dependsOnTask,
        string $dependencyType = TaskDependency::TYPE_FINISH_TO_START
    ): TaskDependency {
        if ($task->id === $dependsOnTask->id) {
            throw new InvalidArgumentException('A task cannot depend on itself.');
        }

        if ($task->organization_id !== $organization->id || $dependsOnTask->organization_id !== $organization->id) {
            throw new InvalidArgumentException('Both tasks must belong to the same organization.');
        }

        if ($task->project_id !== $dependsOnTask->project_id) {
            throw new InvalidArgumentException('Dependencies can only be established between tasks in the same project.');
        }

        // Check if dependency already exists
        $existing = TaskDependency::where('task_id', $task->id)
            ->where('depends_on_task_id', $dependsOnTask->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Cycle Detection via Depth First Search:
        // If we make $task depend on $dependsOnTask, we must ensure $dependsOnTask does NOT already depend on $task (directly or indirectly).
        if ($this->hasCycle($dependsOnTask->id, $task->id)) {
            throw new InvalidArgumentException('Circular dependency detected! Adding this relationship would create an infinite loop.');
        }

        return TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $dependsOnTask->id,
            'dependency_type' => $dependencyType,
        ]);
    }

    /**
     * Check if startTaskId can reach targetTaskId through existing dependencies.
     */
    private function hasCycle(string $startTaskId, string $targetTaskId, array &$visited = []): bool
    {
        if ($startTaskId === $targetTaskId) {
            return true;
        }

        $visited[$startTaskId] = true;

        // Find all tasks that $startTaskId depends on
        $dependencies = TaskDependency::where('task_id', $startTaskId)->pluck('depends_on_task_id');

        foreach ($dependencies as $nextTaskId) {
            if (! isset($visited[$nextTaskId])) {
                if ($this->hasCycle($nextTaskId, $targetTaskId, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }
}
