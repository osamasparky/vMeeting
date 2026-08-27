<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phase_id' => ['nullable', 'exists:project_phases,id'],
            'milestone_id' => ['nullable', 'exists:project_milestones,id'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'task_type' => ['nullable', 'string', 'in:task,bug,feature,improvement'],
            'status' => ['nullable', 'string', 'in:'.implode(',', Task::STATUSES)],
            'priority' => ['nullable', 'string', 'in:'.implode(',', Task::PRIORITIES)],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
        ];
    }
}
