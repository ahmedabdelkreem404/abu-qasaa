<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'description' => $this->description,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'settings_json' => $this->settings_json ?? [],
            'created_by' => $this->created_by,
            'enabled_modules_count' => $this->enabled_modules_count ?? $this->moduleAssignments?->where('is_enabled', true)->count(),
            'modules' => BusinessUnitModuleResource::collection($this->whenLoaded('moduleAssignments')),
            'settings' => BusinessUnitSettingResource::collection($this->whenLoaded('settings')),
        ];
    }
}
