<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPaymentStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
