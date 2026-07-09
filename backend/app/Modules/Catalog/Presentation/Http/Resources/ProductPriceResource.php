<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'price_list_id' => $this->price_list_id,
            'price_list' => $this->whenLoaded('priceList', fn () => PriceListResource::make($this->priceList)),
            'min_quantity' => $this->min_quantity,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_active' => $this->is_active,
        ];
    }
}
