<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Core\Application\Actions\BaseAction;

class CreatePriceListAction extends BaseAction
{
    public function handle(mixed ...$arguments): PriceList
    {
        return PriceList::query()->create($arguments[0]);
    }
}
