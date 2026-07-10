<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManualPaymentProofResource extends JsonResource
{
    public function __construct($resource, private readonly bool $public = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? ['id' => $this->businessUnit->id, 'slug' => $this->businessUnit->slug, 'name_ar' => $this->businessUnit->name_ar, 'name_en' => $this->businessUnit->name_en] : null),
            'order' => $this->whenLoaded('order', fn () => $this->order ? ['id' => $this->order->id, 'order_number' => $this->order->order_number, 'status' => $this->order->status, 'payment_status' => $this->order->payment_status, 'customer_name' => $this->order->customer_name, 'customer_phone' => $this->order->customer_phone, 'grand_total' => $this->order->grand_total, 'currency' => $this->order->currency] : null),
            'payment_method' => $this->whenLoaded('paymentMethod', fn () => $this->paymentMethod ? new PaymentMethodResource($this->paymentMethod, $this->public) : null),
            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? PaymentResource::make($this->payment) : null),
            'status' => $this->status,
            'amount' => $this->amount,
            'payer_name' => $this->payer_name,
            'sender_account' => $this->sender_account,
            'transaction_reference' => $this->transaction_reference,
            'proof_image' => $this->proof_image,
            'notes' => $this->notes,
            'admin_notes' => $this->when(! $this->public, $this->admin_notes),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? ['id' => $this->reviewer->id, 'name' => $this->reviewer->name] : null),
            'reviewed_at' => $this->reviewed_at,
            'rejected_reason' => $this->when(! $this->public, $this->rejected_reason),
            'created_at' => $this->created_at,
        ];
    }
}
