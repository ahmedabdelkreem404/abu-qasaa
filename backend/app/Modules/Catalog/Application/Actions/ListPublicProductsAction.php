<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class ListPublicProductsAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$businessUnit, $filters] = $arguments + [null, []];
        $query = Product::query()
            ->with(['category', 'brand', 'images', 'variants', 'prices.priceList'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'published')
            ->where('visibility', 'public');

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($inner) => $inner->where('slug', $filters['category']));
        }
        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (! empty($filters['brand'])) {
            $query->whereHas('brand', fn ($inner) => $inner->where('slug', $filters['brand']));
        }
        if (array_key_exists('is_featured', $filters)) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($inner) => $inner->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        if (! empty($filters['min_price'])) {
            $query->where('base_price', '>=', $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        return $query->latest('published_at')->paginate((int) ($filters['per_page'] ?? 15));
    }
}
