<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class ListWholesaleCustomersAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments;
        $query = Customer::query()->with(['businessUnit', 'priceList'])->whereNotNull('wholesale_status')->latest();

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            $ids = $user->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id');
            $query->whereIn('business_unit_id', $ids);
        }
        foreach (['business_unit_id', 'wholesale_status', 'price_list_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        foreach (['phone', 'company_name'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', '%'.$filters[$field].'%');
            }
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
