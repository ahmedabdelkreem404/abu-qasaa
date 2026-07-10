<?php

namespace App\Modules\Payments\Application\Services;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Application\DTOs\PaymentInitiationResult;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

interface PaymentProviderInterface
{
    public function initiate(Order $order, PaymentMethod $method, Payment $payment): PaymentInitiationResult;

    public function verifyCallback(array $payload, array $headers = []): bool;
}
