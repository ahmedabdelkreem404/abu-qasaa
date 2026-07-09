<?php

namespace App\Modules\CMS\Domain\Enums;

enum CmsPageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
