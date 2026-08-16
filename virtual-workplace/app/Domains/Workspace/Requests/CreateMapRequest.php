<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'floor_id' => ['required', 'uuid', 'exists:floors,id'],
            'name' => ['required', 'string', 'max:255'],
            'width' => ['nullable', 'integer', 'min:10', 'max:200'],
            'height' => ['nullable', 'integer', 'min:10', 'max:200'],
            'tile_size' => ['nullable', 'integer', 'in:16,32,48,64'],
            'layout_data' => ['nullable', 'array'],
        ];
    }
}
