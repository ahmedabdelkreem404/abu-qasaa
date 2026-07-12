<?php

namespace App\Modules\Catalog\Domain\Enums;

enum CollectionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
