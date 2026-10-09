<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;

class StartTimerRequest extends FormRequest
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
            'task_id' => ['nullable', $this->existsInOrganization('tasks')],
            'user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_billable' => ['nullable', 'boolean'],
        ];
    }
}
