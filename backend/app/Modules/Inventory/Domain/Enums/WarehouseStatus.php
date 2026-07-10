<?php

namespace App\Modules\Inventory\Domain\Enums;

enum WarehouseStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
