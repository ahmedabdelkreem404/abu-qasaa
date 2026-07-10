<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Payments\Domain\Enums\ManualPaymentProofStatus;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Domain\Enums\PaymentTransactionType;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use Illuminate\Support\Facades\DB;

class RejectManualPaymentProofAction
{
    public function handle(ManualPaymentProof $proof, array $data, User $user): ManualPaymentProof
    {
        return DB::transaction(function () use ($proof, $data, $user): ManualPaymentProof {
            $proof->update([
                'status' => ManualPaymentProofStatus::Rejected->value,
                'rejected_reason' => $data['rejected_reason'],
                'admin_notes' => $data['admin_notes'] ?? null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            $payment = $proof->payment;
            $payment->update(['status' => PaymentStatus::Failed->value, 'failed_at' => now(), 'updated_by' => $user->id]);
            $payment->transactions()->create([
                'type' => PaymentTransactionType::ManualRejected->value,
                'status' => PaymentStatus::Failed->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'reference' => $proof->transaction_reference,
                'processed_at' => now(),
                'created_by' => $user->id,
            ]);
            $proof->order->update(['payment_status' => PaymentStatus::Unpaid->value]);

            return $proof->refresh()->load(['businessUnit', 'order', 'paymentMethod', 'payment', 'reviewer']);
        });
    }
}
