<?php

namespace App\Modules\CMS\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactInquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'source_page' => $this->source_page,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'metadata_json' => $this->metadata_json ?? [],
            'created_at' => $this->created_at,
        ];
    }
}
