<?php

namespace App\Modules\Catalog\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CorporateGiftInquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'product_id' => $this->product_id,
            'product_collection_id' => $this->product_collection_id,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'quantity' => $this->quantity,
            'budget_range' => $this->budget_range,
            'occasion' => $this->occasion,
            'message' => $this->message,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'metadata_json' => $this->metadata_json,
            'created_at' => $this->created_at,
        ];
    }
}
