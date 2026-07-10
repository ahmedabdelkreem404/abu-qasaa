<?php

namespace App\Modules\Inventory\Domain\Enums;

enum StockReservationStatus: string
{
    case Reserved = 'reserved';
    case Released = 'released';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
}
