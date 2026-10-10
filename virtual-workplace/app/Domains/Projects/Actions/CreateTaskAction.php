<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskTemplate;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;

class CreateTaskAction
{
    public function execute(array $data, Organization $organization, User $creator): Task
    {
        // A task template fills whatever the form left empty.
        $template = ! empty($data['template_id']) ? TaskTemplate::find($data['template_id']) : null;
        if ($template) {
            foreach (['title', 'description', 'priority', 'task_type', 'estimated_hours'] as $field) {
                if (empty($data[$field]) && isset($template->payload[$field])) {
                    $data[$field] = $template->payload[$field];
                }
            }
        }

        return DB::transaction(function () use ($data, $organization, $creator, $template) {
            $projectId = $data['project_id'];

            // Calculate next auto-incremented task_number per project
            $nextNumber = Task::where('project_id', $projectId)->max('task_number') + 1;
            $nextOrder = $data['order'] ?? (Task::where('project_id', $projectId)->max('order') + 1);

            // Without an explicit status, start in the project's default state.
            $defaultState = empty($data['status'])
                ? ProjectState::where('project_id', $projectId)->where('is_default', true)->first()
                : null;

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
                'status' => $data['status'] ?? $defaultState?->group ?? Task::STATUS_BACKLOG,
                'state_id' => $defaultState?->id,
                'priority' => $data['priority'] ?? Task::PRIORITY_MEDIUM,
                // With several assignees the first is the primary one.
                'assignee_id' => $data['assignee_ids'][0] ?? $data['assignee_id'] ?? null,
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

            if (! empty($data['assignee_ids'])) {
                $task->assignees()->syncWithoutDetaching(array_values(array_unique($data['assignee_ids'])));
            }
            if (! empty($data['label_ids'])) {
                $task->labels()->sync($data['label_ids']);
            }
            if ($template) {
                if (! empty($template->payload['story_points'])) {
                    $task->forceFill(['story_points' => $template->payload['story_points']])->saveQuietly();
                }
                foreach (array_values($template->payload['checklist'] ?? []) as $i => $title) {
                    $task->checklistItems()->create(['organization_id' => $organization->id, 'title' => $title, 'order' => $i + 1]);
                }
                // Template labels match this project's labels by name.
                $labelIds = ProjectLabel::where('project_id', $task->project_id)->whereIn('name', $template->payload['labels'] ?? [])->pluck('id');
                $task->labels()->syncWithoutDetaching($labelIds);
            }

            foreach ($task->assignees()->pluck('users.id') as $assigneeId) {
                NotificationService::notifyTaskAssigned($task, $assigneeId, $creator);
            }

            return $task->load(['project', 'assignee', 'assignees:id,name,email', 'labels', 'reporter', 'phase', 'milestone', 'team', 'parentTask']);
        });
    }
}
