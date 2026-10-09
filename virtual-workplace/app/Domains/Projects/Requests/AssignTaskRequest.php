<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Requests\Concerns\ScopesToOrganization;
use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    use ScopesToOrganization;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assignee_id' => ['nullable', $this->organizationMember()],
        ];
    }
}
