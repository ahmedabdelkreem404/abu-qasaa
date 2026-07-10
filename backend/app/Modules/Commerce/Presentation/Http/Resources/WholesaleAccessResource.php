<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesaleAccessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_method' => 'phone_token',
            'token' => $this->resource['token'],
            'customer' => WholesaleCustomerResource::make($this->resource['customer'], true),
            'expires_hint' => null,
        ];
    }
}
