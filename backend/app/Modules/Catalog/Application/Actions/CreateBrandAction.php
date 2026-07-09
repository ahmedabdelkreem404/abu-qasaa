<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Brand;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateBrandAction extends BaseAction
{
    public function handle(mixed ...$arguments): Brand
    {
        return Brand::query()->create($arguments[0]);
    }
}
