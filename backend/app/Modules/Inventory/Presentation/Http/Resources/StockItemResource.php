<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $available = (float) $this->quantity_on_hand - (float) $this->quantity_reserved;

        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse', fn () => $this->warehouse ? ['id' => $this->warehouse->id, 'name_ar' => $this->warehouse->name_ar, 'name_en' => $this->warehouse->name_en, 'slug' => $this->warehouse->slug] : null),
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => $this->product ? ['id' => $this->product->id, 'slug' => $this->product->slug, 'name_ar' => $this->product->name_ar, 'name_en' => $this->product->name_en] : null),
            'product_variant_id' => $this->product_variant_id,
            'variant' => $this->whenLoaded('variant', fn () => $this->variant ? ['id' => $this->variant->id, 'name_ar' => $this->variant->name_ar, 'name_en' => $this->variant->name_en, 'sku' => $this->variant->sku] : null),
            'sku' => $this->sku,
            'quantity_on_hand' => $this->quantity_on_hand,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_available' => number_format($available, 3, '.', ''),
            'reorder_level' => $this->reorder_level,
            'max_stock_level' => $this->max_stock_level,
            'low_stock' => $available <= (float) $this->reorder_level,
            'last_movement_at' => $this->last_movement_at,
        ];
    }
}
