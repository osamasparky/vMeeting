<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
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
            'type' => ['nullable', 'string', 'in:meeting,private,manager,support,client,reception'],
            'access_mode' => ['nullable', 'string', 'in:public,private,role,invite'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'color' => ['nullable', 'string', 'max:32'],
            'bounds' => ['required', 'array'],
            'bounds.x' => ['required', 'numeric'],
            'bounds.y' => ['required', 'numeric'],
            'bounds.width' => ['required', 'numeric', 'min:1'],
            'bounds.height' => ['required', 'numeric', 'min:1'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
