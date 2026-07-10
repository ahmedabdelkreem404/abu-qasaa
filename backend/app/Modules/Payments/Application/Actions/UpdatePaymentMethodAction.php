<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class UpdatePaymentMethodAction
{
    public function handle(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->update($data);

        return $paymentMethod->refresh()->load('businessUnit');
    }
}
