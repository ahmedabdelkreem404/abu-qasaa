<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesalePricingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->resource['product_id'],
            'product_slug' => $this->resource['product_slug'],
            'name_ar' => $this->resource['name_ar'],
            'name_en' => $this->resource['name_en'],
            'sku' => $this->resource['sku'],
            'currency' => $this->resource['currency'],
            'base_price' => $this->resource['base_price'],
            'wholesale_price' => $this->resource['wholesale_price'],
            'price_list_id' => $this->resource['price_list_id'],
            'price_list_type' => $this->resource['price_list_type'],
            'price_audience' => $this->resource['price_audience'],
            'min_quantity_applied' => $this->resource['min_quantity_applied'],
            'price_source' => $this->resource['price_source'],
        ];
    }
}
