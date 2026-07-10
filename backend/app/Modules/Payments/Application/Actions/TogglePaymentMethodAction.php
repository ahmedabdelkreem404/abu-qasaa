<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class TogglePaymentMethodAction
{
    public function handle(PaymentMethod $paymentMethod): PaymentMethod
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return $paymentMethod->refresh()->load('businessUnit');
    }
}
