<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use App\Modules\Catalog\Domain\Enums\CategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(CategoryStatus::values())],
            'sort_order' => ['nullable', 'integer'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_description_ar' => ['nullable', 'string'],
            'seo_description_en' => ['nullable', 'string'],
        ];
    }
}
