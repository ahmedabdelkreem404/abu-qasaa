<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentTransactionType: string
{
    case ManualProofSubmitted = 'manual_proof_submitted';
    case ManualApproved = 'manual_approved';
    case ManualRejected = 'manual_rejected';
    case CodSelected = 'cod_selected';
    case AdminMarkPaid = 'admin_mark_paid';
    case AdminMarkFailed = 'admin_mark_failed';
}
