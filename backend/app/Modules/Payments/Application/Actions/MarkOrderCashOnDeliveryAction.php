<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class MarkOrderCashOnDeliveryAction
{
    public function handle(Order $order, ?User $user = null): Payment
    {
        $method = PaymentMethod::query()
            ->where('business_unit_id', $order->business_unit_id)
            ->where('type', PaymentMethodType::CashOnDelivery->value)
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($order, $method, $user): Payment {
            $payment = Payment::query()->updateOrCreate(
                ['order_id' => $order->id, 'payment_method_id' => $method->id],
                [
                    'business_unit_id' => $order->business_unit_id,
                    'customer_id' => $order->customer_id,
                    'method_type' => $method->type,
                    'method_key' => $method->key,
                    'status' => PaymentStatus::Pending->value,
                    'amount' => $order->grand_total,
                    'currency' => $order->currency,
                    'created_by' => $user?->id,
                    'updated_by' => $user?->id,
                ],
            );

            $payment->transactions()->create([
                'type' => PaymentTransactionType::CodSelected->value,
                'status' => PaymentStatus::Pending->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'processed_at' => now(),
                'created_by' => $user?->id,
            ]);
            $order->update(['payment_status' => PaymentStatus::Pending->value]);

            return $payment->load(['businessUnit', 'order', 'customer', 'paymentMethod', 'transactions']);
        });
    }
}
