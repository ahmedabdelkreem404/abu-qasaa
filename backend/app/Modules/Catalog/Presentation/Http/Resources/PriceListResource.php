<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? [
                'id' => $this->businessUnit->id,
                'slug' => $this->businessUnit->slug,
                'name_ar' => $this->businessUnit->name_ar,
                'name_en' => $this->businessUnit->name_en,
            ] : null),
            'name' => $this->name,
            'key' => $this->key,
            'type' => $this->type,
            'description' => $this->description,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ];
    }
}
