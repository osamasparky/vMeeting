<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;

class LogManualTimeRequest extends FormRequest
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
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after:started_at'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['nullable', 'boolean'],
            // Whose time this is; the owner may log it for any member. Defaults to the caller.
            'user_id' => ['nullable', 'string', $this->organizationMember()],
        ];
    }
}
