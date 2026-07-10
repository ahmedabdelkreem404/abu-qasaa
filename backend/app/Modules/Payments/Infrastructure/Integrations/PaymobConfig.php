<?php

namespace App\Modules\Payments\Infrastructure\Integrations;

class PaymobConfig
{
    public function __construct(
        public readonly ?string $apiKey,
        public readonly ?string $integrationId,
        public readonly ?string $iframeId,
        public readonly ?string $hmacSecret,
        public readonly string $baseUrl,
        public readonly ?string $callbackUrl,
        public readonly ?string $returnUrl,
        public readonly string $currency,
        public readonly bool $fakeMode,
        public readonly int $timeout,
    ) {}

    public static function fromConfig(array $overrides = []): self
    {
        return new self(
            apiKey: $overrides['api_key'] ?? config('paymob.api_key'),
            integrationId: $overrides['integration_id'] ?? config('paymob.integration_id'),
            iframeId: $overrides['iframe_id'] ?? config('paymob.iframe_id'),
            hmacSecret: $overrides['hmac_secret'] ?? config('paymob.hmac_secret'),
            baseUrl: rtrim($overrides['base_url'] ?? config('paymob.base_url'), '/'),
            callbackUrl: $overrides['callback_url'] ?? config('paymob.callback_url'),
            returnUrl: $overrides['return_url'] ?? config('paymob.return_url'),
            currency: $overrides['currency'] ?? config('paymob.currency', 'EGP'),
            fakeMode: (bool) ($overrides['fake_mode'] ?? config('paymob.fake_mode')),
            timeout: (int) ($overrides['timeout'] ?? config('paymob.timeout', 15)),
        );
    }
}
