<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;

class CreateTaskAction
{
    public function execute(array $data, Organization $organization, User $creator): Task
    {
        return DB::transaction(function () use ($data, $organization, $creator) {
            $projectId = $data['project_id'];

            // Calculate next auto-incremented task_number per project
            $nextNumber = Task::where('project_id', $projectId)->max('task_number') + 1;
            $nextOrder = $data['order'] ?? (Task::where('project_id', $projectId)->max('order') + 1);

            $task = Task::create([
                'organization_id' => $organization->id,
                'project_id' => $projectId,
                'phase_id' => $data['phase_id'] ?? null,
                'milestone_id' => $data['milestone_id'] ?? null,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'task_number' => $nextNumber,
                'task_type' => $data['task_type'] ?? 'task',
                'status' => $data['status'] ?? Task::STATUS_BACKLOG,
                'priority' => $data['priority'] ?? Task::PRIORITY_MEDIUM,
                'assignee_id' => $data['assignee_id'] ?? null,
                'reporter_id' => $creator->id,
                'team_id' => $data['team_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? 0.00,
                'is_billable' => $data['is_billable'] ?? true,
                'order' => $nextOrder,
                'recurrence_rule' => $data['recurrence_rule'] ?? null,
                'recurrence_interval' => $data['recurrence_interval'] ?? 1,
                'recurrence_ends_at' => $data['recurrence_ends_at'] ?? null,
            ]);

            if (! empty($data['assignee_id'])) {
                NotificationService::notifyTaskAssigned($task, $data['assignee_id'], $creator);
            }

            return $task->load(['project', 'assignee', 'reporter', 'phase', 'milestone', 'team', 'parentTask']);
        });
    }
}
