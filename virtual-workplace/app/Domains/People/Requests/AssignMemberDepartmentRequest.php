<?php

namespace App\Domains\People\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignMemberDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'job_title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
