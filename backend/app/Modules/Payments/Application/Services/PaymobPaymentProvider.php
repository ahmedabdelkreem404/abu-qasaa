<?php

namespace App\Modules\Payments\Application\Services;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Application\DTOs\PaymentInitiationResult;
use App\Modules\Payments\Infrastructure\Integrations\PaymobClient;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class PaymobPaymentProvider implements PaymentProviderInterface
{
    public function __construct(private readonly PaymobClient $client, private readonly PaymobSignatureVerifier $verifier) {}

    public function initiate(Order $order, PaymentMethod $method, Payment $payment): PaymentInitiationResult
    {
        return $this->client->createIntention($order, $method, $payment);
    }

    public function verifyCallback(array $payload, array $headers = []): bool
    {
        return $this->verifier->verify($payload, $headers);
    }
}
