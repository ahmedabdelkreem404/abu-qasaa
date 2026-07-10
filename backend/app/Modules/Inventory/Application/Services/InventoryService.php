<?php

namespace App\Modules\Inventory\Application\Services;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Commerce\Infrastructure\Models\OrderItem;
use App\Modules\Inventory\Domain\Enums\StockMovementReason;
use App\Modules\Inventory\Domain\Enums\StockMovementType;
use App\Modules\Inventory\Domain\Enums\StockReservationStatus;
use App\Modules\Inventory\Domain\Enums\StockTransferStatus;
use App\Modules\Inventory\Infrastructure\Models\StockItem;
use App\Modules\Inventory\Infrastructure\Models\StockMovement;
use App\Modules\Inventory\Infrastructure\Models\StockReservation;
use App\Modules\Inventory\Infrastructure\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function inventoryEnabled(BusinessUnit $businessUnit): bool
    {
        return $businessUnit->moduleAssignments()->whereHas('activityModule', fn ($query) => $query->where('key', 'inventory'))->where('is_enabled', true)->exists()
            && (bool) $this->settingValue($businessUnit, 'inventory_enabled');
    }

    public function receive(array $data, ?User $user = null, StockMovementReason $reason = StockMovementReason::PurchaseReceipt): StockItem
    {
        return DB::transaction(function () use ($data, $user, $reason): StockItem {
            $stockItem = $this->stockItem($data, true);
            $before = (float) $stockItem->quantity_on_hand;
            $after = $before + (float) $data['quantity'];
            $stockItem->update(['quantity_on_hand' => $after, 'last_movement_at' => now(), 'sku' => $data['sku'] ?? $stockItem->sku]);
            $this->movement($stockItem, StockMovementType::Receive, $reason, (float) $data['quantity'], $before, $after, $data['note'] ?? null, $user);

            return $stockItem->refresh()->load(['warehouse', 'product', 'variant']);
        });
    }

    public function adjust(array $data, ?User $user = null): StockItem
    {
        return DB::transaction(function () use ($data, $user): StockItem {
            $stockItem = $this->stockItem($data, true);
            $quantity = (float) $data['quantity'];
            $before = (float) $stockItem->quantity_on_hand;
            $type = $data['type'];
            $after = $type === StockMovementType::AdjustmentIn->value ? $before + $quantity : $before - $quantity;
            if ($after < (float) $stockItem->quantity_reserved) {
                throw ValidationException::withMessages(['quantity' => ['Cannot reduce stock below reserved quantity.']]);
            }
            $stockItem->update(['quantity_on_hand' => $after, 'last_movement_at' => now()]);
            $this->movement($stockItem, StockMovementType::from($type), StockMovementReason::ManualAdjustment, $quantity, $before, $after, $data['note'] ?? null, $user);

            return $stockItem->refresh()->load(['warehouse', 'product', 'variant']);
        });
    }

    public function reserveForOrder(Order $order): void
    {
        $order->loadMissing(['businessUnit', 'items']);
        if (! $this->inventoryEnabled($order->businessUnit) || $order->stockReservations()->where('status', StockReservationStatus::Reserved->value)->exists()) {
            return;
        }

        DB::transaction(function () use ($order): void {
            foreach ($order->items as $item) {
                $stockItem = $this->selectSellableStockItem($order->business_unit_id, $item);
                if (! $stockItem || $stockItem->quantity_available < (float) $item->quantity) {
                    throw ValidationException::withMessages(['stock' => ['Some items are no longer available in the requested quantity.']]);
                }
                $before = (float) $stockItem->quantity_reserved;
                $after = $before + (float) $item->quantity;
                $stockItem->update(['quantity_reserved' => $after, 'last_movement_at' => now()]);
                StockReservation::query()->create([
                    'business_unit_id' => $order->business_unit_id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'warehouse_id' => $stockItem->warehouse_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'status' => StockReservationStatus::Reserved->value,
                    'reserved_at' => now(),
                ]);
                $this->movement($stockItem, StockMovementType::Reserve, StockMovementReason::OrderReserved, (float) $item->quantity, $before, $after, 'Order reserved.', null, Order::class, $order->id);
            }
        });
    }

    public function releaseOrderReservations(Order $order, bool $cancelled = false): void
    {
        DB::transaction(function () use ($order, $cancelled): void {
            foreach ($order->stockReservations()->where('status', StockReservationStatus::Reserved->value)->get() as $reservation) {
                $stockItem = StockItem::query()->where('warehouse_id', $reservation->warehouse_id)->where('product_id', $reservation->product_id)->where('product_variant_id', $reservation->product_variant_id)->first();
                if (! $stockItem) {
                    continue;
                }
                $before = (float) $stockItem->quantity_reserved;
                $after = max(0, $before - (float) $reservation->quantity);
                $stockItem->update(['quantity_reserved' => $after, 'last_movement_at' => now()]);
                $reservation->update(['status' => $cancelled ? StockReservationStatus::Cancelled->value : StockReservationStatus::Released->value, 'released_at' => now()]);
                $this->movement($stockItem, StockMovementType::ReleaseReservation, StockMovementReason::OrderCancelled, (float) $reservation->quantity, $before, $after, 'Order reservation released.', null, Order::class, $order->id);
            }
        });
    }

    public function fulfillOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($order->stockReservations()->where('status', StockReservationStatus::Reserved->value)->get() as $reservation) {
                $stockItem = StockItem::query()->where('warehouse_id', $reservation->warehouse_id)->where('product_id', $reservation->product_id)->where('product_variant_id', $reservation->product_variant_id)->first();
                if (! $stockItem) {
                    continue;
                }
                $before = (float) $stockItem->quantity_on_hand;
                $after = $before - (float) $reservation->quantity;
                if ($after < 0) {
                    throw ValidationException::withMessages(['stock' => ['Cannot fulfill order because stock would become negative.']]);
                }
                $stockItem->update(['quantity_on_hand' => $after, 'quantity_reserved' => max(0, (float) $stockItem->quantity_reserved - (float) $reservation->quantity), 'last_movement_at' => now()]);
                $reservation->update(['status' => StockReservationStatus::Fulfilled->value, 'fulfilled_at' => now()]);
                $this->movement($stockItem, StockMovementType::Sale, StockMovementReason::OrderFulfilled, (float) $reservation->quantity, $before, $after, 'Order stock fulfilled.', null, Order::class, $order->id);
            }
        });
    }

    public function completeTransfer(StockTransfer $transfer, ?User $user = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $user): StockTransfer {
            $transfer->loadMissing('items');
            foreach ($transfer->items as $item) {
                $from = $this->stockItem(['business_unit_id' => $transfer->business_unit_id, 'warehouse_id' => $transfer->from_warehouse_id, 'product_id' => $item->product_id, 'product_variant_id' => $item->product_variant_id], false);
                if (! $from || $from->quantity_available < (float) $item->quantity) {
                    throw ValidationException::withMessages(['items' => ['Source warehouse does not have enough available stock.']]);
                }
            }
            foreach ($transfer->items as $item) {
                $from = $this->stockItem(['business_unit_id' => $transfer->business_unit_id, 'warehouse_id' => $transfer->from_warehouse_id, 'product_id' => $item->product_id, 'product_variant_id' => $item->product_variant_id], false);
                $to = $this->stockItem(['business_unit_id' => $transfer->business_unit_id, 'warehouse_id' => $transfer->to_warehouse_id, 'product_id' => $item->product_id, 'product_variant_id' => $item->product_variant_id, 'sku' => $item->sku], true);
                $fromBefore = (float) $from->quantity_on_hand;
                $fromAfter = $fromBefore - (float) $item->quantity;
                $from->update(['quantity_on_hand' => $fromAfter, 'last_movement_at' => now()]);
                $this->movement($from, StockMovementType::TransferOut, StockMovementReason::Transfer, (float) $item->quantity, $fromBefore, $fromAfter, 'Transfer out.', $user, StockTransfer::class, $transfer->id);
                $toBefore = (float) $to->quantity_on_hand;
                $toAfter = $toBefore + (float) $item->quantity;
                $to->update(['quantity_on_hand' => $toAfter, 'last_movement_at' => now()]);
                $this->movement($to, StockMovementType::TransferIn, StockMovementReason::Transfer, (float) $item->quantity, $toBefore, $toAfter, 'Transfer in.', $user, StockTransfer::class, $transfer->id);
            }
            $transfer->update(['status' => StockTransferStatus::Completed->value, 'completed_by' => $user?->id, 'completed_at' => now()]);

            return $transfer->refresh()->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant']);
        });
    }

    public function availability(BusinessUnit $businessUnit, Product $product): array
    {
        if (! $this->inventoryEnabled($businessUnit)) {
            return ['inventory_enabled' => false, 'in_stock' => true, 'available_quantity' => null, 'variants' => []];
        }
        $items = StockItem::query()->where('business_unit_id', $businessUnit->id)->where('product_id', $product->id)->with('variant')->get();
        $available = $items->sum(fn (StockItem $item) => max(0, $item->quantity_available));

        return [
            'inventory_enabled' => true,
            'in_stock' => $available > 0,
            'available_quantity' => $available,
            'variants' => $items->whereNotNull('product_variant_id')->map(fn (StockItem $item) => ['product_variant_id' => $item->product_variant_id, 'sku' => $item->sku, 'in_stock' => $item->quantity_available > 0, 'available_quantity' => $item->quantity_available])->values(),
        ];
    }

    public function stockItem(array $data, bool $create): ?StockItem
    {
        $query = StockItem::query()->where('warehouse_id', $data['warehouse_id'])->where('product_id', $data['product_id'])->where('product_variant_id', $data['product_variant_id'] ?? null);
        $stockItem = $query->first();
        if (! $stockItem && $create) {
            $stockItem = StockItem::query()->create([
                'business_unit_id' => $data['business_unit_id'],
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'sku' => $data['sku'] ?? null,
            ]);
        }

        return $stockItem;
    }

    private function selectSellableStockItem(int $businessUnitId, OrderItem $item): ?StockItem
    {
        return StockItem::query()
            ->where('business_unit_id', $businessUnitId)
            ->where('product_id', $item->product_id)
            ->where('product_variant_id', $item->product_variant_id)
            ->whereHas('warehouse', fn ($query) => $query->where('status', 'active')->where('is_sellable', true))
            ->orderByRaw('(select is_default from warehouses where warehouses.id = stock_items.warehouse_id) desc')
            ->get()
            ->first(fn (StockItem $stockItem) => $stockItem->quantity_available >= (float) $item->quantity);
    }

    private function movement(StockItem $stockItem, StockMovementType $type, StockMovementReason $reason, float $quantity, float $before, float $after, ?string $note = null, ?User $user = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        StockMovement::query()->create([
            'business_unit_id' => $stockItem->business_unit_id,
            'warehouse_id' => $stockItem->warehouse_id,
            'product_id' => $stockItem->product_id,
            'product_variant_id' => $stockItem->product_variant_id,
            'stock_item_id' => $stockItem->id,
            'type' => $type->value,
            'reason' => $reason->value,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
            'created_by' => $user?->id,
        ]);
    }

    private function settingValue(BusinessUnit $businessUnit, string $key): mixed
    {
        $value = $businessUnit->settings()->where('key', $key)->value('value');
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }
}
