<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'image' => $this->image,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_description_en' => $this->seo_description_en,
        ];
    }
}
