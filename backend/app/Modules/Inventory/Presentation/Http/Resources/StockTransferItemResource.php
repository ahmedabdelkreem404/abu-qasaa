<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_transfer_id' => $this->stock_transfer_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
        ];
    }
}
