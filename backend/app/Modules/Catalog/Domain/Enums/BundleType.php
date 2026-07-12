<?php

namespace App\Modules\Catalog\Domain\Enums;

enum BundleType: string
{
    case FixedBox = 'fixed_box';
    case CorporateBox = 'corporate_box';
    case SeasonalBox = 'seasonal_box';
    case SimpleBundle = 'simple_bundle';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
