<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class PublishProductAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        $product = $arguments[0];
        $product->update(['status' => 'published', 'visibility' => 'public', 'published_at' => now()]);

        return $product->refresh();
    }
}
