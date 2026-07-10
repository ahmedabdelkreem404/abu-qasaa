<?php

namespace App\Modules\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
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
            'key' => $this->key,
            'type' => $this->type,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'instructions_ar' => $this->instructions_ar,
            'instructions_en' => $this->instructions_en,
            'destination_account' => $this->destination_account,
            'destination_account_name' => $this->destination_account_name,
            'config_json' => $this->when(! $this->public, $this->config_json),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
