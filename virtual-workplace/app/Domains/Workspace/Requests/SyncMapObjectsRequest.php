<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncMapObjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objects' => ['required', 'array'],
            'objects.*.id' => ['nullable', 'uuid'],
            'objects.*.type' => ['required', 'string', 'max:64'],
            'objects.*.name' => ['nullable', 'string', 'max:255'],
            'objects.*.position' => ['required', 'array'],
            'objects.*.position.x' => ['required', 'numeric'],
            'objects.*.position.y' => ['required', 'numeric'],
            'objects.*.size' => ['nullable', 'array'],
            'objects.*.collision' => ['nullable', 'boolean'],
            'objects.*.interaction_config' => ['nullable', 'array'],
        ];
    }
}
