<?php

namespace App\Modules\Commerce\Domain\Enums;

enum PriceAudience: string
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
