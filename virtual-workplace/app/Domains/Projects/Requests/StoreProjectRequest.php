<?php

namespace App\Domains\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via EnsurePermission middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['nullable', 'string', 'in:planning,active,on_hold,completed,cancelled'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'planned_hours' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:16'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required', 'exists:users,id'],
            'members.*.project_role' => ['nullable', 'string', 'in:manager,lead,contributor,viewer'],
            'members.*.cost_rate' => ['nullable', 'numeric', 'min:0'],
            'members.*.billing_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
