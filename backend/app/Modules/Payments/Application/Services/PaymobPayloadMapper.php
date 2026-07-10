<?php

namespace App\Modules\Payments\Application\Services;

use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;

class PaymobPayloadMapper
{
    public function paymentId(array $payload): ?int
    {
        $value = data_get($payload, 'extras.payment_id') ?? data_get($payload, 'obj.payment_key_claims.extra.payment_id') ?? data_get($payload, 'payment_id');

        return $value ? (int) $value : null;
    }

    public function status(array $payload): PaymentStatus
    {
        if ((bool) ($payload['success'] ?? data_get($payload, 'obj.success', false))) {
            return PaymentStatus::Paid;
        }

        $providerStatus = strtolower((string) ($payload['status'] ?? data_get($payload, 'obj.status', '')));
        if (str_contains($providerStatus, 'cancel') || (bool) ($payload['is_voided'] ?? false)) {
            return PaymentStatus::Cancelled;
        }

        return PaymentStatus::Failed;
    }

    public function transactionType(PaymentStatus $status): PaymentTransactionType
    {
        return match ($status) {
            PaymentStatus::Paid => PaymentTransactionType::PaymobPaid,
            PaymentStatus::Cancelled => PaymentTransactionType::PaymobCancelled,
            default => PaymentTransactionType::PaymobFailed,
        };
    }

    public function providerTransactionId(array $payload): ?string
    {
        return (string) (data_get($payload, 'id') ?? data_get($payload, 'obj.id') ?? data_get($payload, 'transaction_id') ?? '');
    }

    public function providerOrderId(array $payload): ?string
    {
        return (string) (data_get($payload, 'order.id') ?? data_get($payload, 'obj.order.id') ?? data_get($payload, 'order') ?? '');
    }

    public function providerStatus(array $payload): string
    {
        return (string) ($payload['status'] ?? data_get($payload, 'obj.status') ?? (($payload['success'] ?? false) ? 'success' : 'failed'));
    }
}
