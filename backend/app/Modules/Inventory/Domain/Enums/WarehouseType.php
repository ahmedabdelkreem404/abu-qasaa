<?php

namespace App\Modules\Inventory\Domain\Enums;

enum WarehouseType: string
{
    case Main = 'main';
    case Branch = 'branch';
    case Distribution = 'distribution';
    case Returns = 'returns';
    case Virtual = 'virtual';
}
