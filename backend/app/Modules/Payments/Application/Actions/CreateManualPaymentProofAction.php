<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Domain\Enums\ManualPaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class CreateManualPaymentProofAction
{
    public function handle(BusinessUnit $businessUnit, Order $order, array $data, ?User $user = null): ManualPaymentProof
    {
        $method = PaymentMethod::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->when($data['payment_method_id'] ?? null, fn ($q, $id) => $q->orWhereKey($id))
                ->when($data['method_key'] ?? null, fn ($q, $key) => $q->orWhere('key', $key)))
            ->firstOrFail();

        abort_unless(PaymentMethodType::from($method->type)->isManualProof(), 422);
        abort_if(abs((float) $order->grand_total - (float) $data['amount']) > 0.01, 422);

        return DB::transaction(function () use ($businessUnit, $order, $data, $method, $user): ManualPaymentProof {
            $payment = Payment::query()->updateOrCreate(
                ['order_id' => $order->id, 'payment_method_id' => $method->id],
                [
                    'business_unit_id' => $businessUnit->id,
                    'customer_id' => $order->customer_id,
                    'method_type' => $method->type,
                    'method_key' => $method->key,
                    'status' => PaymentStatus::Pending->value,
                    'amount' => $data['amount'],
                    'currency' => $order->currency,
                    'reference' => $data['transaction_reference'] ?? null,
                    'created_by' => $user?->id,
                    'updated_by' => $user?->id,
                ],
            );

            $proof = ManualPaymentProof::query()->create([
                'business_unit_id' => $businessUnit->id,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'payment_method_id' => $method->id,
                'status' => ManualPaymentProofStatus::PendingReview->value,
                'amount' => $data['amount'],
                'payer_name' => $data['payer_name'] ?? null,
                'sender_account' => $data['sender_account'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'proof_image' => $data['proof_image'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $payment->transactions()->create([
                'type' => PaymentTransactionType::ManualProofSubmitted->value,
                'status' => PaymentStatus::Pending->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $proof->transaction_reference,
                'processed_at' => now(),
                'created_by' => $user?->id,
            ]);

            $order->update(['payment_status' => PaymentStatus::Pending->value]);

            return $proof->load(['businessUnit', 'order', 'paymentMethod', 'payment']);
        });
    }
}
