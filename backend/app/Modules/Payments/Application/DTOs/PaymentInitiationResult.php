<?php

namespace App\Modules\Payments\Application\DTOs;

class PaymentInitiationResult
{
    public function __construct(
        public readonly ?string $checkoutUrl,
        public readonly ?string $iframeUrl,
        public readonly ?string $providerOrderId,
        public readonly ?string $providerSessionId,
        public readonly ?string $providerReference,
        public readonly string $providerStatus,
        public readonly array $rawResponse,
    ) {}
}
