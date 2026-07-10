<?php

namespace App\Modules\Payments\Domain\Enums;

enum PaymentMethodType: string
{
    case VodafoneCash = 'vodafone_cash';
    case Instapay = 'instapay';
    case BankTransfer = 'bank_transfer';
    case CashOnDelivery = 'cash_on_delivery';
    case PaymobPlaceholder = 'paymob_placeholder';

    public function isManualProof(): bool
    {
        return in_array($this, [self::VodafoneCash, self::Instapay, self::BankTransfer], true);
    }
}
