<?php

namespace App\Modules\Payments\Application\Actions;

use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;

class GetManualPaymentProofAction
{
    public function handle(ManualPaymentProof $proof): ManualPaymentProof
    {
        return $proof->load(['businessUnit', 'order.items', 'paymentMethod', 'payment.transactions', 'reviewer']);
    }
}
