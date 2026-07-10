<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? ['id' => $this->businessUnit->id, 'slug' => $this->businessUnit->slug, 'name_ar' => $this->businessUnit->name_ar, 'name_en' => $this->businessUnit->name_en] : null),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'status' => $this->status,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_ar' => $this->address_ar,
            'address_en' => $this->address_en,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'area' => $this->area,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_public' => $this->is_public,
            'sort_order' => $this->sort_order,
        ];
    }
}
