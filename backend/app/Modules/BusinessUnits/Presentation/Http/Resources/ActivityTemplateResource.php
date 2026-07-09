<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'default_modules_json' => $this->default_modules_json ?? [],
            'default_settings_json' => $this->default_settings_json ?? [],
            'is_active' => (bool) $this->is_active,
        ];
    }
}
