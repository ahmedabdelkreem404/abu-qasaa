<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Core\Application\Actions\BaseAction;

class ArchivePriceListAction extends BaseAction
{
    public function handle(mixed ...$arguments): PriceList
    {
        $priceList = $arguments[0];
        $priceList->update(['is_active' => false]);

        return $priceList->refresh();
    }
}
