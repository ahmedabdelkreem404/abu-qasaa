<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'product_id' => $this->product_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'bundle_type' => $this->bundle_type,
            'pricing_mode' => $this->pricing_mode,
            'fixed_price' => $this->fixed_price,
            'is_active' => $this->is_active,
            'metadata_json' => $this->metadata_json,
            'items' => ProductBundleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
