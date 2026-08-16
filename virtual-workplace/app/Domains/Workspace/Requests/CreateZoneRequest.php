<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'map_id' => ['required', 'uuid', 'exists:maps,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:movement,audio,interaction,quiet'],
            'shape_type' => ['nullable', 'string', 'in:rectangle,polygon'],
            'shape_data' => ['required', 'array'],
            'audible_radius' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
