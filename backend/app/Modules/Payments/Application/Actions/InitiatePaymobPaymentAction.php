<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Application\Services\PaymobPaymentProvider;
use App\Modules\Payments\Application\Services\PaymobSignatureVerifier;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentProvider;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Integrations\PaymobClient;
use App\Modules\Payments\Infrastructure\Integrations\PaymobConfig;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class InitiatePaymobPaymentAction
{
    public function handle(BusinessUnit $businessUnit, Order $order, array $data): Payment
    {
        abort_if(in_array($order->status, ['cancelled', 'archived', 'delivered'], true), 403);
        abort_if($order->payment_status === PaymentStatus::Paid->value, 422);
        abort_unless((float) $order->grand_total > 0, 422);

        $method = PaymentMethod::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->when($data['payment_method_id'] ?? null, fn ($q, $id) => $q->orWhereKey($id))
                ->when($data['method_key'] ?? null, fn ($q, $key) => $q->orWhere('key', $key)))
            ->firstOrFail();

        abort_unless(PaymentMethodType::from($method->type)->isPaymob(), 422);

        return DB::transaction(function () use ($businessUnit, $order, $method): Payment {
            $payment = Payment::query()->updateOrCreate(
                ['order_id' => $order->id, 'payment_method_id' => $method->id, 'provider' => PaymentProvider::Paymob->value],
                [
                    'business_unit_id' => $businessUnit->id,
                    'customer_id' => $order->customer_id,
                    'method_type' => $method->type,
                    'method_key' => $method->key,
                    'status' => PaymentStatus::Pending->value,
                    'amount' => $order->grand_total,
                    'currency' => $order->currency,
                ],
            );

            $provider = new PaymobPaymentProvider(new PaymobClient(PaymobConfig::fromConfig($method->config_json ?? [])), new PaymobSignatureVerifier);
            $result = $provider->initiate($order->loadMissing('businessUnit'), $method, $payment);

            $payment->update([
                'provider_order_id' => $result->providerOrderId,
                'provider_session_id' => $result->providerSessionId,
                'provider_reference' => $result->providerReference,
                'provider_status' => $result->providerStatus,
                'checkout_url' => $result->checkoutUrl ?? $result->iframeUrl,
                'expires_at' => now()->addMinutes(30),
                'metadata_json' => ['iframe_url' => $result->iframeUrl],
            ]);

            $payment->transactions()->create([
                'type' => PaymentTransactionType::PaymobInitiated->value,
                'status' => PaymentStatus::Pending->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'provider' => PaymentProvider::Paymob->value,
                'provider_order_id' => $result->providerOrderId,
                'provider_status' => $result->providerStatus,
                'raw_payload_json' => $result->rawResponse,
                'processed_at' => now(),
            ]);

            $updates = ['payment_status' => PaymentStatus::Pending->value];
            if ($order->status === 'pending_review') {
                $updates['status'] = 'pending_payment';
            }
            $order->update($updates);

            return $payment->refresh()->load(['businessUnit', 'order', 'customer', 'paymentMethod', 'transactions']);
        });
    }
}
