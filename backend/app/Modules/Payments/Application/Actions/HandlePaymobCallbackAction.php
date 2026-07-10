<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\OrderStatusHistory;
use App\Modules\Payments\Application\Services\PaymobPayloadMapper;
use App\Modules\Payments\Application\Services\PaymobPaymentProvider;
use App\Modules\Payments\Application\Services\PaymobSignatureVerifier;
use App\Modules\Payments\Domain\Enums\PaymentProvider;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Integrations\PaymobClient;
use App\Modules\Payments\Infrastructure\Integrations\PaymobConfig;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Support\Facades\DB;

class HandlePaymobCallbackAction
{
    public function __construct(private readonly PaymobPayloadMapper $mapper) {}

    public function handle(array $payload, array $headers = []): Payment
    {
        $provider = new PaymobPaymentProvider(new PaymobClient(PaymobConfig::fromConfig()), new PaymobSignatureVerifier);
        abort_unless($provider->verifyCallback($payload, $headers), 403);

        $paymentId = $this->mapper->paymentId($payload);
        $payment = Payment::query()
            ->with('order')
            ->when($paymentId, fn ($query) => $query->whereKey($paymentId))
            ->when(! $paymentId, fn ($query) => $query->where('provider_reference', $this->mapper->providerTransactionId($payload)))
            ->firstOrFail();

        return DB::transaction(function () use ($payment, $payload): Payment {
            $status = $this->mapper->status($payload);
            $transactionId = $this->mapper->providerTransactionId($payload);
            $alreadyRecorded = $payment->transactions()->whereIn('type', [PaymentTransactionType::PaymobPaid->value, PaymentTransactionType::PaymobFailed->value, PaymentTransactionType::PaymobCancelled->value])->where('provider_transaction_id', $transactionId)->exists();

            $payment->transactions()->firstOrCreate(
                ['type' => PaymentTransactionType::PaymobCallbackReceived->value, 'provider_transaction_id' => $transactionId],
                [
                    'status' => PaymentStatus::Pending->value,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'provider' => PaymentProvider::Paymob->value,
                    'provider_order_id' => $this->mapper->providerOrderId($payload),
                    'provider_status' => $this->mapper->providerStatus($payload),
                    'raw_payload_json' => $payload,
                    'processed_at' => now(),
                    'verified_at' => now(),
                ],
            );

            if (! $alreadyRecorded) {
                $payment->transactions()->create([
                    'type' => $this->mapper->transactionType($status)->value,
                    'status' => $status->value,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'provider' => PaymentProvider::Paymob->value,
                    'provider_transaction_id' => $transactionId,
                    'provider_order_id' => $this->mapper->providerOrderId($payload),
                    'provider_status' => $this->mapper->providerStatus($payload),
                    'raw_payload_json' => $payload,
                    'processed_at' => now(),
                    'verified_at' => now(),
                ]);
            }

            $updates = ['status' => $status->value, 'provider_payment_id' => $transactionId, 'provider_status' => $this->mapper->providerStatus($payload)];
            if ($status === PaymentStatus::Paid) {
                $updates['paid_at'] = now();
            } elseif (in_array($status, [PaymentStatus::Failed, PaymentStatus::Cancelled], true)) {
                $updates['failed_at'] = now();
            }
            $payment->update($updates);

            $order = $payment->order;
            if ($order) {
                if ($status === PaymentStatus::Paid) {
                    $oldStatus = $order->status;
                    $orderUpdates = ['payment_status' => PaymentStatus::Paid->value];
                    if (in_array($order->status, ['pending_review', 'pending_payment'], true)) {
                        $orderUpdates['status'] = 'confirmed';
                        $orderUpdates['confirmed_at'] = now();
                    }
                    $order->update($orderUpdates);
                    if (($orderUpdates['status'] ?? null) === 'confirmed') {
                        OrderStatusHistory::query()->create(['order_id' => $order->id, 'from_status' => $oldStatus, 'to_status' => 'confirmed', 'note' => 'Paymob payment confirmed.', 'changed_by' => null]);
                    }
                } elseif ($order->payment_status !== PaymentStatus::Paid->value) {
                    $order->update(['payment_status' => $status === PaymentStatus::Cancelled ? PaymentStatus::Cancelled->value : PaymentStatus::Failed->value]);
                }
            }

            return $payment->refresh()->load(['businessUnit', 'order', 'customer', 'paymentMethod', 'transactions']);
        });
    }
}
