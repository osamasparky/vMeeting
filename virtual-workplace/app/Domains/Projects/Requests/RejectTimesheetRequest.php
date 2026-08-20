<?php

namespace App\Domains\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectTimesheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
