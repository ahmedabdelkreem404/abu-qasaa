<?php

namespace App\Modules\Catalog\Domain\Enums;

enum ProductType: string
{
    case Simple = 'simple';
    case Variable = 'variable';
    case Bundle = 'bundle';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
