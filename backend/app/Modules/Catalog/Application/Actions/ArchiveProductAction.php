<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class ArchiveProductAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        $product = $arguments[0];
        $product->update(['status' => 'archived']);
        $product->delete();

        return $product;
    }
}
