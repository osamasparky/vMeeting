<?php

namespace App\Domains\Workspace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFurnitureItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:furniture_categories,id'],
            'image' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg,svg', 'max:4096'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
            'width' => ['required', 'integer', 'min:1', 'max:10'],
            'height' => ['required', 'integer', 'min:1', 'max:10'],
            'collision' => ['nullable', 'boolean'],
            'colors' => ['nullable', 'string'],
        ];
    }
}
