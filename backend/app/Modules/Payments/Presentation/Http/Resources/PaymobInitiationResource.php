<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymobInitiationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment_id' => $this->id,
            'payment_status' => $this->status,
            'checkout_url' => $this->checkout_url,
            'iframe_url' => $this->metadata_json['iframe_url'] ?? null,
            'provider_reference' => $this->provider_reference,
            'message' => 'Redirect to Paymob to complete payment. Final status is confirmed by backend callback only.',
        ];
    }
}
