<?php

namespace App\Console\Commands;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Remind assignees the day before a task is due, and once when it becomes
 * overdue. Safe to run as often as the scheduler likes: each task remembers
 * which due date it was already reminded about.
 */
class SendTaskDueRemindersCommand extends Command
{
    protected $signature = 'tasks:due-reminders';

    protected $description = 'Notify assignees about tasks due tomorrow and tasks that just became overdue';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $soon = 0;
        $late = 0;

        // withoutGlobalScopes() drops the tenant scope (no request here) but also the
        // archive scope, so archived tasks and tasks of archived projects are excluded by hand.
        $open = fn () => Task::withoutGlobalScopes()
            ->whereNull('tasks.archived_at')
            ->where(fn ($q) => $q->whereNull('project_id')
                ->orWhereNotIn('project_id', DB::table('projects')->whereNotNull('archived_at')->select('id')))
            ->whereNotNull('due_date')
            ->where('status', '!=', Task::STATUS_DONE)
            ->with(['project:id,name', 'assignees:id']);

        $open()->whereDate('due_date', $tomorrow->toDateString())
            ->where(fn ($q) => $q->whereNull('due_reminder_sent_for')->orWhereColumn('due_reminder_sent_for', '!=', 'due_date'))
            ->chunkById(200, function ($tasks) use (&$soon) {
                foreach ($tasks as $task) {
                    $this->notify($task, 'task_due_soon',
                        fn () => __('⏰ ":task" is due tomorrow', ['task' => $task->title]),
                        fn () => __('Task ":task" in project ":project" is due on :date.', ['task' => $task->title, 'project' => $task->project?->name, 'date' => $task->due_date->toDateString()]));
                    $task->forceFill(['due_reminder_sent_for' => $task->due_date])->saveQuietly();
                    $soon++;
                }
            });

        $open()->whereDate('due_date', '<', $today->toDateString())
            ->where(fn ($q) => $q->whereNull('overdue_notice_sent_for')->orWhereColumn('overdue_notice_sent_for', '!=', 'due_date'))
            ->chunkById(200, function ($tasks) use (&$late) {
                foreach ($tasks as $task) {
                    $this->notify($task, 'task_overdue',
                        fn () => __('⚠️ ":task" is overdue', ['task' => $task->title]),
                        fn () => __('Task ":task" in project ":project" was due on :date.', ['task' => $task->title, 'project' => $task->project?->name, 'date' => $task->due_date->toDateString()]));
                    $task->forceFill(['overdue_notice_sent_for' => $task->due_date])->saveQuietly();
                    $late++;
                }
            });

        $this->info("Due-tomorrow reminders: {$soon}; overdue notices: {$late}.");

        return self::SUCCESS;
    }

    private function notify(Task $task, string $type, \Closure $title, \Closure $body): void
    {
        $recipients = $task->assignees->pluck('id')->push($task->assignee_id)->filter()->unique();

        foreach ($recipients as $userId) {
            NotificationService::send(
                $userId,
                $type,
                $title,
                $body,
                NotificationService::taskUrl($task->project_id, $task->id),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $type === 'task_overdue' ? '⚠️' : '⏰',
                $task->organization_id,
            );
        }
    }
}
