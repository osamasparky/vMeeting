<?php

namespace App\Domains\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['nullable', 'boolean'],
        ];
    }
}
