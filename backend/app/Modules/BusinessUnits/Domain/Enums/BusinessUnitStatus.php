<?php

namespace App\Modules\BusinessUnits\Domain\Enums;

enum BusinessUnitStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
