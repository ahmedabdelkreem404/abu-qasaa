<?php

namespace App\Modules\ServicesRfq\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqRequestResource extends JsonResource
{
    public function __construct($resource, private readonly bool $public = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'service_id' => $this->service_id,
            'rfq_number' => $this->rfq_number,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'origin_country' => $this->origin_country,
            'destination_country' => $this->destination_country,
            'shipping_method' => $this->shipping_method,
            'incoterm' => $this->incoterm,
            'currency' => $this->currency,
            'expected_date' => $this->expected_date,
            'status' => $this->status,
            'notes' => $this->when(! $this->public, $this->notes),
            'submitted_at' => $this->submitted_at,
            'items' => RfqItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
