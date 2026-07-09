<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use App\Modules\Core\Application\Actions\BaseAction;

class UpsertProductVariantsAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$product, $variants] = $arguments;
        $product->variants()->delete();
        foreach ($variants as $index => $variant) {
            ProductVariant::query()->create([...$variant, 'product_id' => $product->id, 'sort_order' => $variant['sort_order'] ?? $index]);
        }

        return $product->refresh()->load(['variants', 'images', 'prices.priceList']);
    }
}
