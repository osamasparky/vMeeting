<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskComment;
use Illuminate\Support\Str;

/**
 * Posts a task comment and layers on the web UI's richer behavior: parsing
 * mentions (notifying the mentioned org members) and notifying the task's
 * assignee. Built on the plain AddTaskCommentAction so the base
 * create-a-comment behavior isn't duplicated.
 *
 * Note: the API's TaskController::addComment currently uses
 * AddTaskCommentAction directly, without this notification layer — that is
 * a pre-existing behavior divergence between web and API, not something
 * this extraction changes. See Architecture Audit §9.
 */
class PostTaskCommentWithNotificationsAction
{
    public function __construct(private readonly AddTaskCommentAction $addComment) {}

    public function execute(Task $task, User $author, string $body): TaskComment
    {
        $comment = $this->addComment->execute($task, $author, $body);

        $this->notifyMentions($task, $author, $body);
        $this->notifyAssignee($task, $author, $body);

        return $comment;
    }

    private function notifyMentions(Task $task, User $author, string $body): void
    {
        preg_match_all('/@([a-zA-Z0-9_\.\-\p{Arabic}]+)/u', $body, $matches);
        if (empty($matches[1])) {
            return;
        }

        $mentionedHandles = array_unique($matches[1]);
        $orgUsers = User::whereIn('id', function ($q) use ($task) {
            $q->select('user_id')->from('organization_members')->where('organization_id', $task->organization_id);
        })->get();

        foreach ($mentionedHandles as $handle) {
            $target = $orgUsers->first(function ($u) use ($handle) {
                return str_contains(strtolower($u->name), strtolower($handle)) || str_contains(strtolower($u->email), strtolower($handle));
            });

            if ($target && $target->id !== $author->id) {
                NotificationService::notifyCustom(
                    $target->id,
                    'task_mention',
                    __('🔔 :name mentioned you in task ":task"', ['name' => $author->name, 'task' => $task->title]),
                    Str::limit($body, 120),
                    ['task_id' => $task->id, 'project_id' => $task->project_id],
                    $author->id
                );
            }
        }
    }

    private function notifyAssignee(Task $task, User $author, string $body): void
    {
        if ($task->assignee_id && $task->assignee_id !== $author->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_comment',
                __('💬 New comment on your task ":task" by :name', ['task' => $task->title, 'name' => $author->name]),
                Str::limit($body, 120),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $author->id
            );
        }
    }
}
