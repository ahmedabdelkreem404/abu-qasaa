<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'stock_item_id' => $this->stock_item_id,
            'type' => $this->type,
            'reason' => $this->reason,
            'quantity' => $this->quantity,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
