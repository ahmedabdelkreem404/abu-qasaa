<?php

namespace App\Modules\CMS\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsMenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label_ar' => $this->label_ar,
            'label_en' => $this->label_en,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
