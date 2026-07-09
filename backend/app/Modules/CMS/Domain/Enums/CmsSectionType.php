<?php

namespace App\Modules\CMS\Domain\Enums;

enum CmsSectionType: string
{
    case Hero = 'hero';
    case Text = 'text';
    case ImageText = 'image_text';
    case Cards = 'cards';
    case Stats = 'stats';
    case BusinessUnits = 'business_units';
    case Branches = 'branches';
    case ContactCta = 'contact_cta';
    case Custom = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
