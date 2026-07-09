<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class GetPublicProductBySlugAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$businessUnit, $slug] = $arguments;

        return Product::query()
            ->with(['businessUnit', 'category', 'brand', 'variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'images', 'prices.priceList'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->firstOrFail();
    }
}
