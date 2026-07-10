<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'session_token' => $this->session_token,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'shipping_total' => $this->shipping_total,
            'grand_total' => $this->grand_total,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
