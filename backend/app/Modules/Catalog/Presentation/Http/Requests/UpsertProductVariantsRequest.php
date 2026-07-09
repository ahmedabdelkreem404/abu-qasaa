<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProductVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variants' => ['required', 'array'],
            'variants.*.name_ar' => ['nullable', 'string', 'max:255'],
            'variants.*.name_en' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.barcode' => ['nullable', 'string', 'max:255'],
            'variants.*.option_values_json' => ['nullable', 'array'],
            'variants.*.price_adjustment' => ['nullable', 'numeric'],
            'variants.*.sort_order' => ['nullable', 'integer'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
