<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'country' => $this->country,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'area' => $this->area,
            'street_address' => $this->street_address,
            'building' => $this->building,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'landmark' => $this->landmark,
            'postal_code' => $this->postal_code,
            'is_default' => $this->is_default,
        ];
    }
}
