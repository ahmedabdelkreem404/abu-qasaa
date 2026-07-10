<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Commerce\Infrastructure\Models\OrderStatusHistory;
use App\Modules\Core\Application\Actions\BaseAction;
use App\Modules\Inventory\Application\Services\InventoryService;

class UpdateOrderStatusAction extends BaseAction
{
    public function handle(mixed ...$arguments): Order
    {
        [$order, $status, $note, $user] = $arguments + [null, null, null, null];
        $from = $order->status;
        $order->update(['status' => $status, 'updated_by' => $user instanceof User ? $user->id : null, 'confirmed_at' => $status === 'confirmed' ? now() : $order->confirmed_at]);
        if ($status === 'cancelled') {
            app(InventoryService::class)->releaseOrderReservations($order, true);
        }
        if (in_array($status, ['shipped', 'delivered'], true)) {
            app(InventoryService::class)->fulfillOrder($order);
        }
        OrderStatusHistory::query()->create(['order_id' => $order->id, 'from_status' => $from, 'to_status' => $status, 'note' => $note, 'changed_by' => $user instanceof User ? $user->id : null]);

        return $order->refresh()->load(['businessUnit', 'customer', 'items', 'statusHistories', 'stockReservations']);
    }
}
