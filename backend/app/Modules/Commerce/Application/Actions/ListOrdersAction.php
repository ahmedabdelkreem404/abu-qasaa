<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Application\Actions\BaseAction;

class ListOrdersAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        [$user, $filters] = $arguments + [null, []];
        $query = Order::query()->with(['businessUnit', 'customer', 'stockReservations'])->latest();
        if ($user instanceof User && ! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }
        foreach (['business_unit_id', 'status', 'payment_status', 'fulfillment_status', 'customer_phone', 'order_number'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
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
