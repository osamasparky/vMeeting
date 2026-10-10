<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'title' => ['required_without:template_id', 'nullable', 'string', 'max:255'],
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
            'assignee_ids' => ['nullable', 'array', 'max:20'],
            'assignee_ids.*' => ['string', $this->organizationMember()],
            'template_id' => ['nullable', 'integer', Rule::exists('task_templates', 'id')->where('organization_id', $this->organizationId())->where(fn ($q) => $q->where('project_id', $this->input('project_id'))->orWhereNull('project_id'))],
            'label_ids' => ['nullable', 'array', 'max:20'],
            'label_ids.*' => ['integer', Rule::exists('project_labels', 'id')->where('project_id', $this->input('project_id'))],
        ];
    }
}
