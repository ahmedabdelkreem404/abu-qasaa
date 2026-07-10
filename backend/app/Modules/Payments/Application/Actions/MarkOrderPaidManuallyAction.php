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

class MarkOrderPaidManuallyAction
{
    public function handle(Order $order, array $data, User $user): Payment
    {
        $method = PaymentMethod::query()
            ->where('business_unit_id', $order->business_unit_id)
            ->where('type', PaymentMethodType::CashOnDelivery->value)
            ->first();

        return DB::transaction(function () use ($order, $data, $user, $method): Payment {
            $payment = Payment::query()->create([
                'business_unit_id' => $order->business_unit_id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'payment_method_id' => $method?->id,
                'method_type' => $method?->type ?? PaymentMethodType::CashOnDelivery->value,
                'method_key' => $method?->key ?? 'manual',
                'status' => PaymentStatus::Paid->value,
                'amount' => $data['amount'] ?? $order->grand_total,
                'currency' => $order->currency,
                'paid_at' => now(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $payment->transactions()->create([
                'type' => PaymentTransactionType::AdminMarkPaid->value,
                'status' => PaymentStatus::Paid->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $payment->reference,
                'processed_at' => now(),
                'created_by' => $user->id,
            ]);
            $order->update(['payment_status' => PaymentStatus::Paid->value]);

            return $payment->load(['businessUnit', 'order', 'customer', 'paymentMethod', 'transactions']);
        });
    }
}
