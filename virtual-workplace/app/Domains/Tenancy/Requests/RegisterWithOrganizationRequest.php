<?php

namespace App\Domains\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the combined "sign up and create your organization" form used
 * by the web registration page (as opposed to the API's separate
 * register-then-create-organization endpoints).
 */
class RegisterWithOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'plan_slug' => ['nullable', 'string'],
        ];
    }
}
