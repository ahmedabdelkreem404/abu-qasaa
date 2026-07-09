<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProductPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prices' => ['required', 'array'],
            'prices.*.price_list_id' => ['required', 'exists:price_lists,id'],
            'prices.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'prices.*.min_quantity' => ['nullable', 'integer', 'min:1'],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
            'prices.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.starts_at' => ['nullable', 'date'],
            'prices.*.ends_at' => ['nullable', 'date', 'after:prices.*.starts_at'],
            'prices.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
