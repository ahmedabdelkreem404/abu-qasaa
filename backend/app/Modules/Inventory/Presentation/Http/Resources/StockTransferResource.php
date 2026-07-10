<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'transfer_number' => $this->transfer_number,
            'from_warehouse_id' => $this->from_warehouse_id,
            'to_warehouse_id' => $this->to_warehouse_id,
            'from_warehouse' => $this->whenLoaded('fromWarehouse', fn () => $this->fromWarehouse ? WarehouseResource::make($this->fromWarehouse) : null),
            'to_warehouse' => $this->whenLoaded('toWarehouse', fn () => $this->toWarehouse ? WarehouseResource::make($this->toWarehouse) : null),
            'status' => $this->status,
            'note' => $this->note,
            'items' => StockTransferItemResource::collection($this->whenLoaded('items')),
            'requested_at' => $this->requested_at,
            'approved_at' => $this->approved_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
        ];
    }
}
