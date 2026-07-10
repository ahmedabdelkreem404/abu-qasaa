<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class ListCustomersAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments + [null, []];
        $query = Customer::query()->with('businessUnit')->latest();
        if ($user instanceof User && ! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }
        if (! empty($filters['business_unit_id'])) {
            $query->where('business_unit_id', $filters['business_unit_id']);
        }
        if (! empty($filters['phone'])) {
            $query->where('phone', 'like', '%'.$filters['phone'].'%');
        }
        if (! empty($filters['search'])) {
            $query->where(fn ($inner) => $inner->where('name', 'like', '%'.$filters['search'].'%')->orWhere('phone', 'like', '%'.$filters['search'].'%'));
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
