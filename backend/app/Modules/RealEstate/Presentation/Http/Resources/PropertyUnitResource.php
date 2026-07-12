<?php

namespace App\Modules\RealEstate\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'project_id' => $this->project_id,
            'property_id' => $this->property_id,
            'unit_code' => $this->unit_code,
            'unit_type' => $this->unit_type,
            'status' => $this->status,
            'floor' => $this->floor,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area' => $this->area,
            'garden_area' => $this->garden_area,
            'terrace_area' => $this->terrace_area,
            'price' => $this->price,
            'currency' => $this->currency,
            'down_payment' => $this->down_payment,
            'installment_months' => $this->installment_months,
            'finishing_type' => $this->finishing_type,
            'view_type' => $this->view_type,
            'featured_image' => $this->featured_image,
            'gallery_json' => $this->gallery_json,
            'specs_json' => $this->specs_json,
            'is_featured' => $this->is_featured,
        ];
    }
}
