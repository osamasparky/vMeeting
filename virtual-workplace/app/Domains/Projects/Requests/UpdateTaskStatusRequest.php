<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required_without:state_id', 'string', 'in:'.implode(',', Task::STATUSES)],
            // A project state; its group becomes the status.
            'state_id' => ['nullable', 'integer', Rule::exists('project_states', 'id')->where('project_id', $this->route('task')?->project_id)],
        ];
    }
}
