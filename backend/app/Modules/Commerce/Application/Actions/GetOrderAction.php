<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Application\Actions\BaseAction;

class GetOrderAction extends BaseAction
{
    public function handle(mixed ...$arguments): Order
    {
        return $arguments[0]->load(['businessUnit', 'customer.addresses', 'items', 'stockReservations.warehouse', 'statusHistories.changedBy']);
    }
}
