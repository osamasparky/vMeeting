<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use Illuminate\Support\Facades\DB;

class SetTaskAssigneesAction
{
    /**
     * Replace a task's assignees. The first id becomes the primary assignee
     * (tasks.assignee_id); newly added people are notified.
     *
     * @param  list<string>  $userIds
     */
    public function execute(Task $task, array $userIds, ?User $actor = null): Task
    {
        $userIds = array_values(array_unique(array_filter($userIds)));
        $before = $task->assignees()->pluck('users.id')->all();

        DB::transaction(function () use ($task, $userIds) {
            $primary = $userIds[0] ?? null;
            if ($task->assignee_id !== $primary) {
                // Quiet save: the saved() hook would otherwise re-sync the
                // list from the single primary before we write the full list.
                $task->assignee_id = $primary;
                $task->saveQuietly();
            }
            $task->assignees()->sync($userIds);
        });

        foreach (array_diff($userIds, $before) as $added) {
            NotificationService::notifyTaskAssigned($task, $added, $actor);
        }

        return $task->fresh(['assignee:id,name,email', 'assignees:id,name,email', 'labels']);
    }
}
