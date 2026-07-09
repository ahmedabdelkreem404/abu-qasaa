<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessUnitModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'activity_module_id' => $this->activity_module_id,
            'key' => $this->activityModule?->key,
            'name' => $this->activityModule?->name,
            'category' => $this->activityModule?->category,
            'is_enabled' => (bool) $this->is_enabled,
            'settings_json' => $this->settings_json ?? [],
        ];
    }
}
