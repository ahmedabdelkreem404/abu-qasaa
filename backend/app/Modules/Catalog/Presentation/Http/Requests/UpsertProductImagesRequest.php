<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array'],
            'images.*.image' => ['required', 'string', 'max:2048'],
            'images.*.alt_ar' => ['nullable', 'string', 'max:255'],
            'images.*.alt_en' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer'],
            'images.*.is_primary' => ['nullable', 'boolean'],
        ];
    }
}
