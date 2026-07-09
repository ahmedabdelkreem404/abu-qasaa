<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Brand;
use App\Modules\Core\Application\Actions\BaseAction;

class ArchiveBrandAction extends BaseAction
{
    public function handle(mixed ...$arguments): Brand
    {
        $brand = $arguments[0];
        $brand->update(['status' => 'archived']);
        $brand->delete();

        return $brand;
    }
}
