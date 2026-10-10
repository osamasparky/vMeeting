<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;

class NotifyTaskWatchersAction
{
    /**
     * Notify everyone following a task, except the person who acted and any
     * ids in $except (e.g. people already told through a more specific notice).
     *
     * @param  list<string>  $except
     */
    public function execute(Task $task, string $type, string|\Closure $title, string|\Closure|null $body, ?User $actor = null, array $except = []): int
    {
        $skip = array_filter(array_merge($except, [$actor?->id]));
        $sent = 0;

        foreach ($task->watchers()->pluck('users.id') as $userId) {
            if (in_array($userId, $skip, true)) {
                continue;
            }
            NotificationService::send(
                $userId,
                $type,
                $title,
                $body,
                NotificationService::taskUrl($task->project_id, $task->id),
                ['task_id' => $task->id, 'project_id' => $task->project_id, 'actor_id' => $actor?->id],
                '🔔',
                $task->organization_id,
            );
            $sent++;
        }

        return $sent;
    }
}
