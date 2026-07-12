<?php

namespace App\Modules\Audit\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'old_values_json' => $this->old_values_json,
            'new_values_json' => $this->new_values_json,
            'created_at' => $this->created_at,
        ];
    }
}
