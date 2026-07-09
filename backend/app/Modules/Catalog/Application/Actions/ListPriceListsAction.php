<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Core\Application\Actions\BaseAction;

class ListPriceListsAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments + [null, []];
        $query = PriceList::query()->with('businessUnit')->orderByDesc('is_default')->orderBy('name');
        if ($user instanceof User && ! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }
        if (! empty($filters['business_unit_id'])) {
            $query->where('business_unit_id', $filters['business_unit_id']);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
