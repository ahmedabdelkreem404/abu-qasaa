<?php

namespace App\Modules\CMS\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsPageResource extends JsonResource
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
                'type' => $this->businessUnit->type,
            ] : null),
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'page_type' => $this->page_type,
            'status' => $this->status,
            'excerpt_ar' => $this->excerpt_ar,
            'excerpt_en' => $this->excerpt_en,
            'content_ar' => $this->content_ar,
            'content_en' => $this->content_en,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_description_en' => $this->seo_description_en,
            'featured_image' => $this->featured_image,
            'sort_order' => $this->sort_order,
            'published_at' => $this->published_at,
            'sections' => CmsSectionResource::collection($this->whenLoaded('sections')),
        ];
    }
}
