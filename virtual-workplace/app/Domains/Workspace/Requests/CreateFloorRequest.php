<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
