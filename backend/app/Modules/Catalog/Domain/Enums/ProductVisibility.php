<?php

namespace App\Modules\Catalog\Domain\Enums;

enum ProductVisibility: string
{
    case Public = 'public';
    case Hidden = 'hidden';
    case Private = 'private';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
