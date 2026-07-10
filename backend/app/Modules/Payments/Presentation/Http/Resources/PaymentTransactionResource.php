<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'provider' => $this->provider,
            'provider_transaction_id' => $this->provider_transaction_id,
            'provider_order_id' => $this->provider_order_id,
            'provider_status' => $this->provider_status,
            'payload_json' => $this->payload_json,
            'verified_at' => $this->verified_at,
            'processed_at' => $this->processed_at,
        ];
    }
}
