<?php

namespace App\Modules\Catalog\Domain\Enums;

enum BundlePricingMode: string
{
    case UseParentProductPrice = 'use_parent_product_price';
    case FixedBundlePrice = 'fixed_bundle_price';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
