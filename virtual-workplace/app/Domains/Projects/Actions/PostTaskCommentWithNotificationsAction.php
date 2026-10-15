<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskComment;
use Illuminate\Support\Collection;
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
            fn () => __('💬 New comment on ":task" by :name', ['task' => $task->title, 'name' => $author->name]),
            Str::limit($body, 120),
            $author,
            except: $mentioned,
        );

        return $comment;
    }

    /** @return list<string> ids of the members who were notified as mentioned */
    private function notifyMentions(Task $task, User $author, string $body): array
    {
        $notified = [];
        foreach ($this->mentionedUsers($task, $body) as $target) {
            if ($target->id === $author->id) {
                continue;
            }
            NotificationService::notifyCustom(
                $target->id,
                'task_mention',
                fn () => __('🔔 :name mentioned you in task ":task"', ['name' => $author->name, 'task' => $task->title]),
                Str::limit($body, 120),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $author->id,
                orgId: $task->organization_id,
            );
            $notified[] = $target->id;
        }

        return $notified;
    }

    /**
     * People named after an "@". A full name wins, the longest one first, so
     * "@mohammed samy" reaches Mohammed Samy and not another Mohammed. A
     * single word or e-mail handle counts only when it fits exactly one person.
     *
     * @return Collection<int, User>
     */
    private function mentionedUsers(Task $task, string $body): Collection
    {
        if (! str_contains($body, '@')) {
            return collect();
        }

        $members = User::whereIn('id', function ($q) use ($task) {
            $q->select('user_id')->from('organization_members')->where('organization_id', $task->organization_id);
        })->get(['id', 'name', 'email']);

        $text = mb_strtolower($body);
        $found = collect();
        $offset = 0;
        while (($at = mb_strpos($text, '@', $offset)) !== false) {
            $offset = $at + 1;
            $rest = mb_substr($text, $at + 1);

            $byName = $members->filter(function (User $u) use ($rest) {
                $name = mb_strtolower(trim((string) $u->name));
                if ($name === '' || ! str_starts_with($rest, $name)) {
                    return false;
                }
                $next = mb_substr($rest, mb_strlen($name), 1);

                return $next === '' || ! preg_match('/[\p{L}\p{N}_]/u', $next);
            })->sortByDesc(fn (User $u) => mb_strlen((string) $u->name))->first();

            if ($byName) {
                $found->push($byName);

                continue;
            }

            if (preg_match('/^[\p{L}\p{N}_.\-]+/u', $rest, $m)) {
                $handle = $m[0];
                $hits = $members->filter(fn (User $u) => str_contains(mb_strtolower((string) $u->name), $handle)
                    || str_starts_with(mb_strtolower((string) $u->email), $handle));
                if ($hits->count() === 1) {
                    $found->push($hits->first());
                }
            }
        }

        return $found->unique('id')->values();
    }
}
