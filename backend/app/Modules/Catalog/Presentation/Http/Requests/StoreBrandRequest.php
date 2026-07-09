<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use App\Modules\Catalog\Domain\Enums\BrandStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(BrandStatus::values())],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
