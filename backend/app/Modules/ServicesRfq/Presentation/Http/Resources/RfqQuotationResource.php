<?php

namespace App\Modules\ServicesRfq\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqQuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'rfq_request_id' => $this->rfq_request_id,
            'quotation_number' => $this->quotation_number,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'shipping_total' => $this->shipping_total,
            'grand_total' => $this->grand_total,
            'currency' => $this->currency,
            'items' => $this->whenLoaded('items'),
        ];
    }
}
