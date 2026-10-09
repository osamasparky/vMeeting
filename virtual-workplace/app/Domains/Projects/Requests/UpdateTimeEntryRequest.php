<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    use ScopesToOrganization;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'required', $this->existsInOrganization('projects')],
            'task_id' => ['nullable', $this->existsInOrganization('tasks')],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['nullable', 'boolean'],
        ];
    }
}
