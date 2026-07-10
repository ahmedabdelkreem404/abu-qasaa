<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Order;

class GetOrderPaymentStatusAction
{
    public function handle(Order $order): array
    {
        $payment = $order->payments()->with(['paymentMethod', 'transactions'])->latest()->first();

        return [
            'order' => ['order_number' => $order->order_number, 'status' => $order->status, 'payment_status' => $order->payment_status, 'grand_total' => $order->grand_total, 'currency' => $order->currency],
            'payment' => $payment ? ['id' => $payment->id, 'provider' => $payment->provider, 'method_type' => $payment->method_type, 'status' => $payment->status, 'provider_status' => $payment->provider_status, 'provider_reference' => $payment->provider_reference] : null,
        ];
    }
}
