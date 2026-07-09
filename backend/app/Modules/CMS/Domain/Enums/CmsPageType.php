<?php

namespace App\Modules\CMS\Domain\Enums;

enum CmsPageType: string
{
    case Home = 'home';
    case About = 'about';
    case Contact = 'contact';
    case BusinessUnitLanding = 'business_unit_landing';
    case Standard = 'standard';
    case Custom = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
