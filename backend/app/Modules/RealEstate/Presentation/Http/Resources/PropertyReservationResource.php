<?php

namespace App\Modules\RealEstate\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'lead_id' => $this->lead_id,
            'project_id' => $this->project_id,
            'unit_id' => $this->unit_id,
            'reservation_number' => $this->reservation_number,
            'status' => $this->status,
            'reservation_amount' => $this->reservation_amount,
            'currency' => $this->currency,
            'reserved_at' => $this->reserved_at,
            'expires_at' => $this->expires_at,
        ];
    }
}
