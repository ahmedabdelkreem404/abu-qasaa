<?php

namespace App\Modules\Commerce\Domain\Enums;

enum CustomerType: string
{
    case Individual = 'individual';
    case Shop = 'shop';
    case Company = 'company';
    case Distributor = 'distributor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
