<?php

namespace App\Modules\Core\Domain\Enums;

enum BusinessUnitType: string
{
    case ProductStore = 'product_store';
    case WholesaleStore = 'wholesale_store';
    case ServicesRfq = 'services_rfq';
    case RealEstate = 'real_estate';
    case ContentOnly = 'content_only';
    case Hybrid = 'hybrid';
}
