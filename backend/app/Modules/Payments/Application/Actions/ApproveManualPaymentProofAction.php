<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\OrderStatusHistory;
use App\Modules\Payments\Domain\Enums\ManualPaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use Illuminate\Support\Facades\DB;

class ApproveManualPaymentProofAction
{
    public function handle(ManualPaymentProof $proof, array $data, User $user): ManualPaymentProof
    {
        return DB::transaction(function () use ($proof, $data, $user): ManualPaymentProof {
            $proof->update([
                'status' => ManualPaymentProofStatus::Approved->value,
                'admin_notes' => $data['admin_notes'] ?? null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            $payment = $proof->payment;
            $payment->update(['status' => PaymentStatus::Paid->value, 'paid_at' => now(), 'updated_by' => $user->id]);
            $payment->transactions()->create([
                'type' => PaymentTransactionType::ManualApproved->value,
                'status' => PaymentStatus::Paid->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $proof->transaction_reference,
                'processed_at' => now(),
                'created_by' => $user->id,
            ]);

            $order = $proof->order;
            $oldStatus = $order->status;
            $updates = ['payment_status' => PaymentStatus::Paid->value];
            if (in_array($order->status, ['pending_review', 'pending_payment'], true)) {
                $updates['status'] = 'confirmed';
                $updates['confirmed_at'] = now();
            }
            $order->update($updates);
            if (($updates['status'] ?? null) === 'confirmed') {
                OrderStatusHistory::query()->create(['order_id' => $order->id, 'from_status' => $oldStatus, 'to_status' => 'confirmed', 'note' => 'Payment proof approved.', 'changed_by' => $user->id]);
            }

            return $proof->refresh()->load(['businessUnit', 'order', 'paymentMethod', 'payment', 'reviewer']);
        });
    }
}
