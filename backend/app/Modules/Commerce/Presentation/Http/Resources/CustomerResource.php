<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'tax_number' => $this->tax_number,
            'commercial_record' => $this->commercial_record,
            'approval_status' => $this->approval_status,
            'wholesale_status' => $this->wholesale_status,
            'price_list_id' => $this->price_list_id,
            'approved_at' => $this->approved_at,
            'credit_limit' => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'addresses' => CustomerAddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
