<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? ['id' => $this->businessUnit->id, 'slug' => $this->businessUnit->slug, 'name_ar' => $this->businessUnit->name_ar, 'name_en' => $this->businessUnit->name_en] : null),
            'order' => $this->whenLoaded('order', fn () => $this->order ? ['id' => $this->order->id, 'order_number' => $this->order->order_number, 'status' => $this->order->status, 'payment_status' => $this->order->payment_status, 'grand_total' => $this->order->grand_total, 'currency' => $this->order->currency] : null),
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? ['id' => $this->customer->id, 'name' => $this->customer->name, 'phone' => $this->customer->phone, 'email' => $this->customer->email] : null),
            'payment_method' => $this->whenLoaded('paymentMethod', fn () => $this->paymentMethod ? PaymentMethodResource::make($this->paymentMethod) : null),
            'method_type' => $this->method_type,
            'method_key' => $this->method_key,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'paid_at' => $this->paid_at,
            'failed_at' => $this->failed_at,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'transactions' => PaymentTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
