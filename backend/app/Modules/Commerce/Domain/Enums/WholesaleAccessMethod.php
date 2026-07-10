<?php

namespace App\Modules\Commerce\Domain\Enums;

enum WholesaleAccessMethod: string
{
    case User = 'user';
    case PhoneToken = 'phone_token';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
