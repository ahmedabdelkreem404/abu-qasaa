<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCollectionResource extends JsonResource
{
    public function __construct($resource, private readonly bool $public = false, private readonly bool $showPrices = true)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'image' => $this->image,
            'status' => $this->when(! $this->public, $this->status),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_description_en' => $this->seo_description_en,
            'products' => $this->whenLoaded('items', fn () => $this->items
                ->map(fn ($item) => $item->product)
                ->filter()
                ->values()
                ->map(fn ($product) => (new ProductResource($product, $this->public, $this->showPrices))->resolve())),
        ];
    }
}
