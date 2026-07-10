<?php

namespace App\Modules\Payments\Domain\Enums;

enum ManualPaymentProofStatus: string
{
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
