<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:' . implode(',', Task::STATUSES)],
        ];
    }
}
