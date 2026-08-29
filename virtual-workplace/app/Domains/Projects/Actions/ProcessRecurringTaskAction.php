<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;
use Illuminate\Support\Facades\DB;

class ProcessRecurringTaskAction
{
    /**
     * Generate the next occurrence of a completed recurring task.
     */
    public function execute(Task $task): ?Task
    {
        if (!$task->isRecurring()) {
            return null;
        }

        $nextDueDate = $task->calculateNextDueDate();
        if (!$nextDueDate) {
            return null;
        }

        // Calculate next start date with same duration offset
        $nextStartDate = null;
        if ($task->start_date && $task->due_date) {
            $durationDays = $task->start_date->diffInDays($task->due_date);
            $nextStartDate = $nextDueDate->copy()->subDays($durationDays);
        }

        return DB::transaction(function () use ($task, $nextStartDate, $nextDueDate) {
            $nextNumber = Task::where('project_id', $task->project_id)->max('task_number') + 1;
            $nextOrder = Task::where('project_id', $task->project_id)->max('order') + 1;

            $newTask = Task::create([
                'organization_id'     => $task->organization_id,
                'project_id'          => $task->project_id,
                'phase_id'            => $task->phase_id,
                'milestone_id'        => $task->milestone_id,
                'parent_task_id'      => $task->parent_task_id,
                'title'               => $task->title,
                'description'         => $task->description,
                'task_number'         => $nextNumber,
                'task_type'           => $task->task_type,
                'status'              => Task::STATUS_READY,
                'priority'            => $task->priority,
                'assignee_id'         => $task->assignee_id,
                'reporter_id'         => $task->reporter_id,
                'team_id'             => $task->team_id,
                'start_date'          => $nextStartDate,
                'due_date'            => $nextDueDate,
                'estimated_hours'     => $task->estimated_hours,
                'is_billable'         => $task->is_billable,
                'order'               => $nextOrder,
                'recurrence_rule'     => $task->recurrence_rule,
                'recurrence_interval' => $task->recurrence_interval,
                'recurrence_ends_at'  => $task->recurrence_ends_at,
            ]);

            // Duplicate checklist items uncompleted
            foreach ($task->checklistItems as $item) {
                $newTask->checklistItems()->create([
                    'organization_id' => $task->organization_id,
                    'title'           => $item->title,
                    'is_completed'    => false,
                    'order'           => $item->order ?? 0,
                ]);
            }

            // Mark old task as recurred
            $task->update([
                'last_recurred_at' => now(),
            ]);

            return $newTask->load(['project', 'assignee', 'checklistItems']);
        });
    }
}
