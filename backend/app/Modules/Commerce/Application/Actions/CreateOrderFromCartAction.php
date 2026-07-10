<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Commerce\Infrastructure\Models\OrderItem;
use App\Modules\Commerce\Infrastructure\Models\OrderStatusHistory;
use App\Modules\Core\Application\Actions\BaseAction;
use App\Modules\Inventory\Application\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderFromCartAction extends BaseAction
{
    public function handle(mixed ...$arguments): Order
    {
        [$businessUnit, $cart, $data] = $arguments;
        if ($cart->status !== 'active' || $cart->items()->count() === 0) {
            throw ValidationException::withMessages(['cart' => ['Cart is not active or is empty.']]);
        }

        return DB::transaction(function () use ($businessUnit, $cart, $data): Order {
            $customer = app(FindOrCreateCustomerAction::class)->handle($businessUnit->id, $data['customer']);
            app(CreateCustomerAddressAction::class)->handle($customer, [...$data['shipping_address'], 'type' => 'shipping', 'is_default' => true]);
            $order = Order::query()->create([
                'business_unit_id' => $businessUnit->id,
                'customer_id' => $customer->id,
                'order_number' => $this->number($businessUnit),
                'status' => 'pending_review',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'currency' => $cart->currency,
                'subtotal' => $cart->subtotal,
                'discount_total' => $cart->discount_total,
                'tax_total' => $cart->tax_total,
                'shipping_total' => $cart->shipping_total,
                'grand_total' => $cart->grand_total,
                'customer_name' => $data['customer']['name'],
                'customer_email' => $data['customer']['email'] ?? null,
                'customer_phone' => $data['customer']['phone'],
                'shipping_address_json' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
                'source' => 'public_checkout',
                'placed_at' => now(),
            ]);
            foreach ($cart->items as $item) {
                OrderItem::query()->create($item->only(['product_id', 'product_variant_id', 'sku', 'product_name_ar', 'product_name_en', 'variant_name_ar', 'variant_name_en', 'quantity', 'unit_price', 'subtotal', 'metadata_json']) + ['order_id' => $order->id]);
            }
            app(InventoryService::class)->reserveForOrder($order->load(['businessUnit', 'items']));
            OrderStatusHistory::query()->create(['order_id' => $order->id, 'from_status' => null, 'to_status' => 'pending_review', 'note' => 'Order created from public checkout.']);
            $cart->update(['status' => 'converted', 'customer_id' => $customer->id]);

            return $order->load(['businessUnit', 'customer', 'items', 'statusHistories', 'stockReservations']);
        });
    }

    private function number(BusinessUnit $businessUnit): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $businessUnit->slug), 0, 3));
        $month = now()->format('Ym');
        $count = Order::query()->where('business_unit_id', $businessUnit->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() + 1;

        return sprintf('%s-%s-%06d', $prefix ?: 'ORD', $month, $count);
    }
}
