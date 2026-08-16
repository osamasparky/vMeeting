<?php

namespace App\Domains\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create an org
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:organizations,slug', 'regex:/^[a-z0-9\-]+$/'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ];
    }
}
