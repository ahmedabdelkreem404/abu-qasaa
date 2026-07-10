<?php

namespace App\Modules\Inventory\Domain\Enums;

enum StockTransferStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
