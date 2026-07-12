<?php

namespace App\Modules\RealEstate\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RealEstateLeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'project_id' => $this->project_id,
            'unit_id' => $this->unit_id,
            'source' => $this->source,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'preferred_contact_method' => $this->preferred_contact_method,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'message' => $this->message,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'next_follow_up_at' => $this->next_follow_up_at,
            'created_at' => $this->created_at,
        ];
    }
}
