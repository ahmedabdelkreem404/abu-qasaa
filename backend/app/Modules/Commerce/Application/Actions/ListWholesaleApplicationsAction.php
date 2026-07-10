<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Core\Application\Actions\BaseAction;

class ListWholesaleApplicationsAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments;
        $query = WholesaleApplication::query()->with(['businessUnit', 'customer', 'requestedPriceList'])->latest();

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            $ids = $user->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id');
            $query->whereIn('business_unit_id', $ids);
        }

        foreach (['business_unit_id', 'status', 'requested_price_list_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        foreach (['phone', 'company_name'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', '%'.$filters[$field].'%');
            }
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
