<?php

namespace App\Modules\RealEstate\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RealEstateProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'project_code' => $this->project_code,
            'status' => $this->status,
            'project_type' => $this->project_type,
            'developer_name' => $this->developer_name,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'address' => $this->address,
            'city' => $this->city,
            'governorate' => $this->governorate,
            'featured_image' => $this->featured_image,
            'gallery_json' => $this->gallery_json,
            'amenities_json' => $this->amenities_json,
            'delivery_date' => $this->delivery_date,
            'starting_price' => $this->starting_price,
            'currency' => $this->currency,
            'is_featured' => $this->is_featured,
            'units' => PropertyUnitResource::collection($this->whenLoaded('units')),
        ];
    }
}
