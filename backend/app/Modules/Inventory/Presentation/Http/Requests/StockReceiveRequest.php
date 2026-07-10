<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'sku' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string'],
        ];
    }
}
