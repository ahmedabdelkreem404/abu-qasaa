<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;

class ListPaymentMethodsAction
{
    public function handle(User $user, array $filters = [])
    {
        $query = PaymentMethod::query()
            ->with('businessUnit')
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id')))
            ->when($filters['business_unit_id'] ?? null, fn ($query, $value) => $query->where('business_unit_id', $value))
            ->when($filters['type'] ?? null, fn ($query, $value) => $query->where('type', $value))
            ->orderBy('business_unit_id')
            ->orderBy('sort_order');

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
