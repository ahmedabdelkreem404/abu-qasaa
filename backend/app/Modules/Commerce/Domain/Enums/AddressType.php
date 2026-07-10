<?php

namespace App\Modules\Commerce\Domain\Enums;

enum AddressType: string
{
    case Shipping = 'shipping';
    case Billing = 'billing';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
