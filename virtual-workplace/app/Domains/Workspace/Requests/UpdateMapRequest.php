<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'layout_data' => ['sometimes', 'required', 'array'],
            'width' => ['sometimes', 'integer', 'min:10', 'max:200'],
            'height' => ['sometimes', 'integer', 'min:10', 'max:200'],
            'tile_size' => ['sometimes', 'integer', 'in:16,32,48,64'],
        ];
    }
}
