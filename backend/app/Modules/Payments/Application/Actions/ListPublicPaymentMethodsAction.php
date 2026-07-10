<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class ListPublicPaymentMethodsAction
{
    public function handle(BusinessUnit $businessUnit)
    {
        return PaymentMethod::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('is_active', true)
            ->where('type', '!=', PaymentMethodType::PaymobPlaceholder->value)
            ->orderBy('sort_order')
            ->get();
    }
}
