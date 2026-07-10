<?php

namespace App\Modules\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
