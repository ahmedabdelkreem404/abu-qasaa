<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductImage;
use App\Modules\Core\Application\Actions\BaseAction;

class UpsertProductImagesAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$product, $images] = $arguments;
        $product->images()->delete();
        foreach ($images as $index => $image) {
            ProductImage::query()->create([...$image, 'product_id' => $product->id, 'sort_order' => $image['sort_order'] ?? $index]);
        }

        return $product->refresh()->load(['variants', 'images', 'prices.priceList']);
    }
}
