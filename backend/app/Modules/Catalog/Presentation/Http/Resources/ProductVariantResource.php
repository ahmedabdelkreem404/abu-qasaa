<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'option_values_json' => $this->option_values_json,
            'price_adjustment' => $this->price_adjustment,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
