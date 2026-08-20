<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskComment;

class AddTaskCommentAction
{
    public function execute(Task $task, User $user, string $body): TaskComment
    {
        return TaskComment::create([
            'organization_id' => $task->organization_id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => $body,
        ])->load('user:id,name,email');
    }
}
