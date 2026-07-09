<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureFlagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'key' => $this->key,
            'value' => (bool) $this->value,
            'description' => $this->description,
        ];
    }
}
