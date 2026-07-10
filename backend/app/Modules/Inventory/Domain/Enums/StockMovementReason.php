<?php

namespace App\Modules\Inventory\Domain\Enums;

enum StockMovementReason: string
{
    case OpeningBalance = 'opening_balance';
    case PurchaseReceipt = 'purchase_receipt';
    case ManualAdjustment = 'manual_adjustment';
    case OrderReserved = 'order_reserved';
    case OrderCancelled = 'order_cancelled';
    case OrderFulfilled = 'order_fulfilled';
    case Transfer = 'transfer';
    case Return = 'return';
    case Correction = 'correction';
}
