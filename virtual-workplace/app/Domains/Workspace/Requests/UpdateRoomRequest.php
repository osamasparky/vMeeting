<?php

namespace App\Domains\Workspace\Requests;

use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Requests\Concerns\ValidatesRoomSpacing;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    use ValidatesRoomSpacing;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'in:meeting,private,manager,support,client,reception'],
            'access_mode' => ['sometimes', 'nullable', 'string', 'in:public,private,role,invite'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'color' => ['sometimes', 'nullable', 'string', 'max:32'],
            'bounds' => ['sometimes', 'required', 'array'],
            'bounds.x' => ['required_with:bounds', 'numeric'],
            'bounds.y' => ['required_with:bounds', 'numeric'],
            'bounds.width' => ['required_with:bounds', 'numeric', 'min:1'],
            'bounds.height' => ['required_with:bounds', 'numeric', 'min:1'],
            'bounds.doorSide' => ['nullable', 'string', 'in:auto,top,bottom,left,right'],
            'bounds.doorOffset' => ['nullable', 'numeric', 'min:0.05', 'max:0.95'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->has('bounds')) {
                return;
            }

            /** @var Room $room */
            $room = $this->route('room');
            $this->validateRoomSpacing($validator, $room?->map_id, $room?->id, $this->input('bounds'));
        });
    }
}
