<?php

namespace App\Modules\Commerce\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesaleApplicationResource extends JsonResource
{
    public function __construct($resource, private readonly bool $public = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(! $this->public, $this->id),
            'business_unit_id' => $this->business_unit_id,
            'business_unit' => $this->whenLoaded('businessUnit', fn () => $this->businessUnit ? [
                'id' => $this->businessUnit->id,
                'slug' => $this->businessUnit->slug,
                'name_ar' => $this->businessUnit->name_ar,
                'name_en' => $this->businessUnit->name_en,
            ] : null),
            'customer_id' => $this->when(! $this->public, $this->customer_id),
            'status' => $this->status,
            'applicant_name' => $this->applicant_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'company_name' => $this->company_name,
            'shop_name' => $this->shop_name,
            'tax_number' => $this->when(! $this->public, $this->tax_number),
            'commercial_record' => $this->when(! $this->public, $this->commercial_record),
            'governorate' => $this->governorate,
            'city' => $this->city,
            'address' => $this->when(! $this->public, $this->address),
            'requested_price_list_id' => $this->when(! $this->public, $this->requested_price_list_id),
            'message' => $this->when(! $this->public, $this->message),
            'reviewed_at' => $this->reviewed_at,
            'rejection_reason' => $this->when($this->public && $this->status === 'rejected', 'Your application was not approved. Please contact support for details.'),
            'admin_rejection_reason' => $this->when(! $this->public, $this->rejection_reason),
            'created_at' => $this->created_at,
        ];
    }
}
