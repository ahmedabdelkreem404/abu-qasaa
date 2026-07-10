<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class CreatePaymentMethodAction
{
    public function handle(array $data): PaymentMethod
    {
        return PaymentMethod::query()->create($data)->load('businessUnit');
    }
}
