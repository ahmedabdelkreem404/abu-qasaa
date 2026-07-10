<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? ['id' => $this->branch->id, 'slug' => $this->branch->slug, 'name_ar' => $this->branch->name_ar, 'name_en' => $this->branch->name_en] : null),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'phone' => $this->phone,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'area' => $this->area,
            'is_default' => $this->is_default,
            'is_sellable' => $this->is_sellable,
            'sort_order' => $this->sort_order,
        ];
    }
}
