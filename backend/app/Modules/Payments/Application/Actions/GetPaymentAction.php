<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Infrastructure\Models\Payment;

class GetPaymentAction
{
    public function handle(Payment $payment): Payment
    {
        return $payment->load(['businessUnit', 'order.items', 'customer', 'paymentMethod', 'transactions']);
    }
}
