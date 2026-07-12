<?php

namespace App\Modules\RealEstate\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'lead_id' => $this->lead_id,
            'project_id' => $this->project_id,
            'unit_id' => $this->unit_id,
            'assigned_user_id' => $this->assigned_user_id,
            'scheduled_at' => $this->scheduled_at,
            'duration_minutes' => $this->duration_minutes,
            'location' => $this->location,
            'status' => $this->status,
            'notes' => $this->notes,
            'outcome' => $this->outcome,
        ];
    }
}
