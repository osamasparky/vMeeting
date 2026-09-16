<?php

namespace App\Domains\Workspace\Requests;

use App\Domains\Workspace\Requests\Concerns\ValidatesRoomSpacing;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
{
    use ValidatesRoomSpacing;

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
            'bounds.doorSide' => ['nullable', 'string', 'in:auto,top,bottom,left,right'],
            'bounds.doorOffset' => ['nullable', 'numeric', 'min:0.05', 'max:0.95'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateRoomSpacing($validator, $this->input('map_id'), null, $this->input('bounds'));
        });
    }
}
