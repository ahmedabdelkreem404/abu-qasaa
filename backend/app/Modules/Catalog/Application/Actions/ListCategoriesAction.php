<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Core\Application\Actions\BaseAction;

class ListCategoriesAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments + [null, []];

        $query = Category::query()->with(['businessUnit', 'parent'])->orderBy('sort_order')->orderBy('name_en')->orderBy('name_ar');
        if ($user instanceof User && ! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }
        if (! empty($filters['business_unit_id'])) {
            $query->where('business_unit_id', $filters['business_unit_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
