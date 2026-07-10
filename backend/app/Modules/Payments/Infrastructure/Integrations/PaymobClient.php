<?php

namespace App\Modules\Payments\Infrastructure\Integrations;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Application\DTOs\PaymentInitiationResult;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;

class PaymobClient
{
    public function __construct(private readonly PaymobConfig $config) {}

    public function createIntention(Order $order, PaymentMethod $method, Payment $payment): PaymentInitiationResult
    {
        if ($this->config->fakeMode) {
            $sessionId = 'fake-session-'.$payment->id;
            $checkoutUrl = ($this->config->returnUrl ?: 'http://localhost:3000/payment/paymob/return').'?payment_id='.$payment->id.'&order='.$order->order_number.'&business='.$order->businessUnit?->slug;

            return new PaymentInitiationResult(
                checkoutUrl: $checkoutUrl,
                iframeUrl: null,
                providerOrderId: 'fake-order-'.$order->id,
                providerSessionId: $sessionId,
                providerReference: 'fake-ref-'.$payment->id,
                providerStatus: 'pending',
                rawResponse: ['fake' => true, 'checkout_url' => $checkoutUrl, 'session_id' => $sessionId],
            );
        }

        if (! $this->config->apiKey) {
            throw new PaymobException('Paymob API key is not configured.');
        }

        $payload = [
            'amount' => (int) round(((float) $order->grand_total) * 100),
            'currency' => $method->config_json['currency'] ?? $this->config->currency,
            'payment_methods' => [(int) ($method->config_json['integration_id'] ?? $this->config->integrationId)],
            'items' => [['name' => 'Order '.$order->order_number, 'amount' => (int) round(((float) $order->grand_total) * 100), 'description' => 'Order payment', 'quantity' => 1]],
            'billing_data' => ['first_name' => $order->customer_name, 'last_name' => '-', 'phone_number' => $order->customer_phone, 'email' => $order->customer_email ?: 'customer@example.com'],
            'extras' => ['payment_id' => $payment->id, 'order_id' => $order->id, 'order_number' => $order->order_number],
            'notification_url' => $method->config_json['callback_url'] ?? $this->config->callbackUrl,
            'redirection_url' => $method->config_json['return_url'] ?? $this->config->returnUrl,
        ];

        // TODO(paymob): Reconfirm exact Intention API response keys when Paymob docs are directly accessible.
        $response = Http::timeout($this->config->timeout)
            ->withToken($this->config->apiKey)
            ->post($this->config->baseUrl.'/v1/intention/', $payload);

        if (! $response->successful()) {
            throw new PaymobException('Paymob intention request failed with status '.$response->status());
        }

        $data = $response->json();
        $clientSecret = $data['client_secret'] ?? null;
        $checkoutUrl = $data['checkout_url'] ?? $data['payment_url'] ?? null;
        if (! $checkoutUrl && $clientSecret) {
            $checkoutUrl = 'https://accept.paymob.com/unifiedcheckout/?publicKey='.$clientSecret;
        }

        return new PaymentInitiationResult(
            checkoutUrl: $checkoutUrl,
            iframeUrl: $data['iframe_url'] ?? null,
            providerOrderId: (string) ($data['intention_order_id'] ?? $data['order_id'] ?? ''),
            providerSessionId: (string) ($data['id'] ?? $clientSecret ?? ''),
            providerReference: (string) ($data['id'] ?? $data['payment_keys'][0]['key'] ?? ''),
            providerStatus: (string) ($data['status'] ?? 'pending'),
            rawResponse: $data,
        );
    }
}
