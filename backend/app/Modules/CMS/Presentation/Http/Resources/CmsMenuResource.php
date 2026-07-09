<?php

namespace App\Modules\CMS\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'name' => $this->name,
            'location' => $this->location,
            'is_active' => (bool) $this->is_active,
            'items' => CmsMenuItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
