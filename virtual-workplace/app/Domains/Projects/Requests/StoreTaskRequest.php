<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    use ScopesToOrganization;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', $this->existsInOrganization('projects')],
            'phase_id' => ['nullable', $this->existsInOrganization('project_phases')],
            'milestone_id' => ['nullable', $this->existsInOrganization('project_milestones')],
            'parent_task_id' => ['nullable', $this->existsInOrganization('tasks')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type' => ['nullable', 'string', 'in:task,bug,feature,improvement'],
            'status' => ['nullable', 'string', 'in:'.implode(',', Task::STATUSES)],
            'priority' => ['nullable', 'string', 'in:'.implode(',', Task::PRIORITIES)],
            'assignee_id' => ['nullable', $this->organizationMember()],
            'team_id' => ['nullable', $this->existsInOrganization('teams')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
            'recurrence_rule' => ['nullable', 'string', 'in:'.implode(',', Task::RECURRENCE_RULES)],
            'recurrence_interval' => ['nullable', 'integer', 'min:1'],
            'recurrence_ends_at' => ['nullable', 'date', 'after_or_equal:due_date'],
        ];
    }
}
