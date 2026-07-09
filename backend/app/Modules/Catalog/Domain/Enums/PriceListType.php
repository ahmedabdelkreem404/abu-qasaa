<?php

namespace App\Modules\Catalog\Domain\Enums;

enum PriceListType: string
{
    case Retail = 'retail';
    case Wholesale = 'wholesale';
    case Distributor = 'distributor';
    case Special = 'special';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
