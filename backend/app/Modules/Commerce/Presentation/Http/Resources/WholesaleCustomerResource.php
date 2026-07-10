<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesaleCustomerResource extends JsonResource
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
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? [
                'id' => $this->businessUnit->id,
                'slug' => $this->businessUnit->slug,
                'name_ar' => $this->businessUnit->name_ar,
                'name_en' => $this->businessUnit->name_en,
            ] : null),
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'tax_number' => $this->when(! $this->public, $this->tax_number),
            'commercial_record' => $this->when(! $this->public, $this->commercial_record),
            'wholesale_status' => $this->wholesale_status,
            'approval_status' => $this->approval_status,
            'price_list_id' => $this->price_list_id,
            'price_list' => $this->whenLoaded('priceList', fn () => $this->priceList ? [
                'id' => $this->priceList->id,
                'name' => $this->priceList->name,
                'type' => $this->priceList->type,
            ] : null),
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->when(! $this->public, $this->rejected_at),
            'rejection_reason' => $this->when(! $this->public, $this->rejection_reason),
            'credit_limit' => $this->when(! $this->public, $this->credit_limit),
            'payment_terms' => $this->when(! $this->public, $this->payment_terms),
            'notes' => $this->when(! $this->public, $this->notes),
        ];
    }
}
