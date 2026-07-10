<?php

namespace App\Modules\Inventory\Domain\Enums;

enum StockMovementType: string
{
    case Receive = 'receive';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case Reserve = 'reserve';
    case ReleaseReservation = 'release_reservation';
    case Sale = 'sale';
    case Return = 'return';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Correction = 'correction';
}
