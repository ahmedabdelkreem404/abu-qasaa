<?php

namespace App\Modules\Payments\Application\Services;

class PaymobSignatureVerifier
{
    public function verify(array $payload, array $headers = []): bool
    {
        $secret = (string) config('paymob.hmac_secret');
        if ((bool) config('paymob.fake_mode')) {
            $expected = hash_hmac('sha512', (string) ($payload['provider_reference'] ?? $payload['id'] ?? $payload['transaction_id'] ?? ''), $secret ?: 'fake-paymob-secret');

            return hash_equals($expected, (string) ($payload['hmac'] ?? $payload['signature'] ?? ''));
        }

        if ($secret === '') {
            return false;
        }

        $received = (string) ($payload['hmac'] ?? $payload['signature'] ?? $headers['hmac'] ?? '');
        if ($received === '') {
            return false;
        }

        // TODO(paymob): Confirm exact transaction callback field order from the official HMAC page when accessible.
        $fields = ['amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment', 'is_voided', 'order', 'owner', 'pending', 'source_data_pan', 'source_data_sub_type', 'source_data_type', 'success'];
        $message = collect($fields)->map(fn (string $field) => data_get($payload, $field, ''))->implode('');

        return hash_equals(hash_hmac('sha512', $message, $secret), $received);
    }
}
