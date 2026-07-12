<?php

namespace App\Modules\ServicesRfq\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'category' => $this->category,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'summary_ar' => $this->summary_ar,
            'summary_en' => $this->summary_en,
            'description_ar' => $this->description_ar ?? $this->description,
            'description_en' => $this->description_en,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ];
    }
}
