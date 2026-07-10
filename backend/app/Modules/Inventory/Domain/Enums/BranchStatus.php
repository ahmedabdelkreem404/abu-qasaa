<?php

namespace App\Modules\Inventory\Domain\Enums;

enum BranchStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
