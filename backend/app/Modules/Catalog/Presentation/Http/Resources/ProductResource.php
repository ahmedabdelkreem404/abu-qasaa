<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? [
                'id' => $this->businessUnit->id,
                'slug' => $this->businessUnit->slug,
                'name_ar' => $this->businessUnit->name_ar,
                'name_en' => $this->businessUnit->name_en,
                'type' => $this->businessUnit->type,
            ] : null),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category ? CategoryResource::make($this->category) : null),
            'brand_id' => $this->brand_id,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? BrandResource::make($this->brand) : null),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'product_type' => $this->product_type,
            'status' => $this->when(! $this->public, $this->status),
            'visibility' => $this->when(! $this->public, $this->visibility),
            'short_description_ar' => $this->short_description_ar,
            'short_description_en' => $this->short_description_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'featured_image' => $this->featured_image,
            'base_price' => $this->when($this->showPrices, $this->base_price),
            'compare_at_price' => $this->when($this->showPrices, $this->compare_at_price),
            'cost_price' => $this->when(! $this->public, $this->cost_price),
            'currency' => $this->currency,
            'is_featured' => $this->is_featured,
            'is_taxable' => $this->when(! $this->public, $this->is_taxable),
            'min_order_quantity' => $this->min_order_quantity,
            'max_order_quantity' => $this->max_order_quantity,
            'specs_json' => $this->specs_json,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_description_en' => $this->seo_description_en,
            'published_at' => $this->published_at,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'prices' => $this->when($this->showPrices, ProductPriceResource::collection($this->whenLoaded('prices'))),
        ];
    }
}
