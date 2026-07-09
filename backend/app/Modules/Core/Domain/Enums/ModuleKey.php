<?php

namespace App\Modules\Core\Domain\Enums;

enum ModuleKey: string
{
    case Catalog = 'catalog';
    case Commerce = 'commerce';
    case Inventory = 'inventory';
    case Payments = 'payments';
    case Cms = 'cms';
    case ServicesRfq = 'services_rfq';
    case RealEstate = 'real_estate';
    case Reports = 'reports';
}
