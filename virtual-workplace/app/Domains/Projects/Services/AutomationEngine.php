<?php

namespace App\Domains\Projects\Services;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\ProjectAutomation;
use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs a project's automation rules for a task event.
 *
 * Rules run once the surrounding transaction commits, so they see the task as
 * it was finally saved (labels, assignees and quietly-set states included).
 * Changes made by a rule do not fire further rules — no chains, no loops.
 */
class AutomationEngine
{
    private static bool $running = false;

    /** @var array<string, true> task|trigger pairs already queued in this transaction */
    private static array $queued = [];

    /**
     * @param  array{label_ids?: list<int>, user_ids?: list<string>}  $context
     */
    public static function fire(Task $task, string $trigger, array $context = []): void
    {
        if (self::$running || ! $task->project_id || ! in_array($trigger, ProjectAutomation::TRIGGERS, true)) {
            return;
        }

        $key = $task->id.'|'.$trigger;
        if (isset(self::$queued[$key])) {
            return;
        }
        self::$queued[$key] = true;

        DB::afterCommit(function () use ($task, $trigger, $context, $key) {
            unset(self::$queued[$key]);
            self::run($task->id, $trigger, $context);
        });
    }

    private static function run(string $taskId, string $trigger, array $context): void
    {
        $task = Task::with(['labels:id', 'assignees:id'])->find($taskId);
        if (! $task) {
            return;
        }

        $rules = ProjectAutomation::where('project_id', $task->project_id)
            ->where('trigger', $trigger)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        self::$running = true;
        try {
            foreach ($rules as $rule) {
                try {
                    if (! self::matches($rule, $task, $context)) {
                        continue;
                    }
                    foreach ($rule->actions ?? [] as $action) {
                        self::apply($rule, $task, $action);
                        $task->refresh()->load(['labels:id', 'assignees:id']);
                    }
                    $rule->forceFill(['run_count' => $rule->run_count + 1, 'last_run_at' => now()])->saveQuietly();
                } catch (\Throwable $e) {
                    // One broken rule never blocks the change that triggered it.
                    Log::warning("Automation {$rule->id} failed: ".$e->getMessage());
                }
            }
        } finally {
            self::$running = false;
        }
    }

    public static function matches(ProjectAutomation $rule, Task $task, array $context = []): bool
    {
        foreach ($rule->conditions ?? [] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $ok = match ($key) {
                'state_id' => (int) $task->state_id === (int) $value,
                'priority' => $task->priority === $value,
                // For "label added", the condition is about the label just added.
                'label_id' => isset($context['label_ids'])
                    ? in_array((int) $value, array_map('intval', $context['label_ids']), true)
                    : $task->labels->contains('id', (int) $value),
                'assignee' => match ($value) {
                    'none' => $task->assignees->isEmpty() && ! $task->assignee_id,
                    'any' => $task->assignees->isNotEmpty() || (bool) $task->assignee_id,
                    default => $task->assignee_id === $value || $task->assignees->contains('id', $value),
                },
                default => true,
            };
            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    private static function apply(ProjectAutomation $rule, Task $task, array $action): void
    {
        $value = $action['value'] ?? null;

        switch ($action['type'] ?? null) {
            case 'set_state':
                $state = ProjectState::where('project_id', $task->project_id)->find($value);
                if ($state && $state->id !== $task->state_id) {
                    $task->forceFill([
                        'status' => $state->group,
                        'state_id' => $state->id,
                        'completed_at' => $state->group === Task::STATUS_DONE ? ($task->completed_at ?? now()) : null,
                    ])->save();
                    $task->refreshRollups();
                }
                break;

            case 'set_priority':
                if (in_array($value, Task::PRIORITIES, true)) {
                    $task->update(['priority' => $value]);
                }
                break;

            case 'set_due_in_days':
                $days = max(0, min(365, (int) $value));
                $task->update(['due_date' => now()->addDays($days)->toDateString()]);
                break;

            case 'add_assignee':
                if (! self::isMember($task, $value) || $task->isAssignedTo($value)) {
                    break;
                }
                if (! $task->assignee_id) {
                    $task->forceFill(['assignee_id' => $value])->saveQuietly();
                }
                $task->assignees()->syncWithoutDetaching([$value]);
                $task->addWatchers([$value]);
                NotificationService::notifyTaskAssigned($task, $value);
                break;

            case 'add_label':
                if (ProjectLabel::where('project_id', $task->project_id)->whereKey($value)->exists()) {
                    $task->labels()->syncWithoutDetaching([(int) $value]);
                }
                break;

            case 'add_watcher':
                if (self::isMember($task, $value)) {
                    $task->addWatchers([$value]);
                }
                break;

            case 'notify':
                $recipients = match ($value) {
                    'assignees' => $task->assignees->pluck('id')->push($task->assignee_id)->all(),
                    'reporter' => [$task->reporter_id],
                    'watchers' => $task->watchers()->pluck('users.id')->all(),
                    'manager' => [$task->project?->manager_id],
                    default => self::isMember($task, $value) ? [$value] : [],
                };
                foreach (array_unique(array_filter($recipients)) as $userId) {
                    NotificationService::send(
                        $userId,
                        'task_updated',
                        fn () => __('Automation: :rule', ['rule' => $rule->name]),
                        $task->title,
                        NotificationService::taskUrl($task->project_id, $task->id),
                        ['task_id' => $task->id, 'project_id' => $task->project_id, 'automation_id' => $rule->id],
                        '⚡',
                        $task->organization_id,
                    );
                }
                break;
        }
    }

    private static function isMember(Task $task, mixed $userId): bool
    {
        return is_string($userId) && OrganizationMember::where('organization_id', $task->organization_id)
            ->where('user_id', $userId)->exists();
    }
}
