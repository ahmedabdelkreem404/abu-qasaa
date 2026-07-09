<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use App\Modules\Catalog\Domain\Enums\ProductStatus;
use App\Modules\Catalog\Domain\Enums\ProductType;
use App\Modules\Catalog\Domain\Enums\ProductVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sku' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(ProductType::values())],
            'status' => ['required', Rule::in(ProductStatus::values())],
            'visibility' => ['required', Rule::in(ProductVisibility::values())],
            'short_description_ar' => ['nullable', 'string'],
            'short_description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:2048'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_featured' => ['nullable', 'boolean'],
            'is_taxable' => ['nullable', 'boolean'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'max_order_quantity' => ['nullable', 'integer', 'min:1'],
            'specs_json' => ['nullable', 'array'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_description_ar' => ['nullable', 'string'],
            'seo_description_en' => ['nullable', 'string'],
        ];
    }
}
