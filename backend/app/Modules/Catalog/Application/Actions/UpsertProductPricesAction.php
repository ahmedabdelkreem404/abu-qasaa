<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductPrice;
use App\Modules\Core\Application\Actions\BaseAction;

class UpsertProductPricesAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$product, $prices] = $arguments;
        $product->prices()->delete();
        foreach ($prices as $price) {
            ProductPrice::query()->create([...$price, 'business_unit_id' => $product->business_unit_id, 'product_id' => $product->id]);
        }

        return $product->refresh()->load(['variants', 'images', 'prices.priceList']);
    }
}
