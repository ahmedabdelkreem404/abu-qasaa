<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Domain\Enums\PaymentProvider;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Models\Payment;

class HandlePaymobReturnAction
{
    public function handle(array $payload): ?Payment
    {
        $payment = Payment::query()->whereKey($payload['payment_id'] ?? 0)->first();
        if ($payment) {
            $payment->transactions()->create([
                'type' => PaymentTransactionType::PaymobReturnReceived->value,
                'status' => $payment->status ?: PaymentStatus::Pending->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'provider' => PaymentProvider::Paymob->value,
                'raw_payload_json' => $payload,
                'processed_at' => now(),
            ]);
        }

        return $payment?->load(['businessUnit', 'order', 'paymentMethod']);
    }
}
