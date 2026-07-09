<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class ListProductsAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments + [null, []];
        $query = Product::query()->with(['businessUnit', 'category', 'brand'])->latest();
        if ($user instanceof User && ! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }
        foreach (['business_unit_id', 'category_id', 'brand_id', 'status', 'visibility'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (array_key_exists('is_featured', $filters)) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($inner) => $inner->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
