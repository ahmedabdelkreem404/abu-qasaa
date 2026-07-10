<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Application\Actions\BaseAction;

class CancelOrderAction extends BaseAction
{
    public function handle(mixed ...$arguments): Order
    {
        return app(UpdateOrderStatusAction::class)->handle($arguments[0], 'cancelled', $arguments[1] ?? 'Order cancelled.', $arguments[2] ?? null);
    }
}
