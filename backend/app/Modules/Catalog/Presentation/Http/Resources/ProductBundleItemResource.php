<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBundleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_bundle_id' => $this->product_bundle_id,
            'child_product_id' => $this->child_product_id,
            'child_product_variant_id' => $this->child_product_variant_id,
            'quantity' => $this->quantity,
            'sort_order' => $this->sort_order,
            'metadata_json' => $this->metadata_json,
            'child_product' => $this->whenLoaded('childProduct', fn () => $this->childProduct ? ProductResource::make($this->childProduct) : null),
        ];
    }
}
