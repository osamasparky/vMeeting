<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskComment;
use Illuminate\Support\Str;

/**
 * Posts a task comment and notifies people: anyone @mentioned gets a mention
 * notice, every other follower of the task gets a comment notice. The author
 * starts following the task. Used by both the hub (web) and the API.
 */
class PostTaskCommentWithNotificationsAction
{
    public function __construct(
        private readonly AddTaskCommentAction $addComment,
        private readonly NotifyTaskWatchersAction $notifyWatchers,
    ) {}

    public function execute(Task $task, User $author, string $body): TaskComment
    {
        $comment = $this->addComment->execute($task, $author, $body);
        $task->addWatchers([$author->id]);

        $mentioned = $this->notifyMentions($task, $author, $body);

        $this->notifyWatchers->execute(
            $task,
            'task_comment',
            __('💬 New comment on ":task" by :name', ['task' => $task->title, 'name' => $author->name]),
            Str::limit($body, 120),
            $author,
            except: $mentioned,
        );

        return $comment;
    }

    /** @return list<string> ids of the members who were notified as mentioned */
    private function notifyMentions(Task $task, User $author, string $body): array
    {
        preg_match_all('/@([a-zA-Z0-9_\.\-\p{Arabic}]+)/u', $body, $matches);
        if (empty($matches[1])) {
            return [];
        }

        $mentionedHandles = array_unique($matches[1]);
        $orgUsers = User::whereIn('id', function ($q) use ($task) {
            $q->select('user_id')->from('organization_members')->where('organization_id', $task->organization_id);
        })->get();

        $notified = [];
        foreach ($mentionedHandles as $handle) {
            $target = $orgUsers->first(function ($u) use ($handle) {
                return str_contains(strtolower($u->name), strtolower($handle)) || str_contains(strtolower($u->email), strtolower($handle));
            });

            if ($target && $target->id !== $author->id && ! in_array($target->id, $notified, true)) {
                NotificationService::notifyCustom(
                    $target->id,
                    'task_mention',
                    __('🔔 :name mentioned you in task ":task"', ['name' => $author->name, 'task' => $task->title]),
                    Str::limit($body, 120),
                    ['task_id' => $task->id, 'project_id' => $task->project_id],
                    $author->id,
                    orgId: $task->organization_id,
                );
                $notified[] = $target->id;
            }
        }

        return $notified;
    }
}
