<?php

namespace App\Domains\Guests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestEnterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:100'],
        ];
    }
}
