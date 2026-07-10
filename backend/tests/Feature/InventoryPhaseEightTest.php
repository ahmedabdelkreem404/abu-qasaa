<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Inventory\Infrastructure\Models\StockItem;
use App\Modules\Inventory\Infrastructure\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryPhaseEightTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_inventory_dashboard(): void
    {
        $this->seed();

        $this->getJson('/api/v1/inventory/summary')->assertUnauthorized();
    }

    public function test_admin_can_receive_and_adjust_stock(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $warehouse = Warehouse::query()->where('business_unit_id', $product->business_unit_id)->firstOrFail();

        $this->postJson('/api/v1/inventory/stock-items/receive', [
            'business_unit_id' => $product->business_unit_id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ])->assertCreated()->assertJsonPath('data.quantity_on_hand', '85.000');

        $this->postJson('/api/v1/inventory/stock-items/adjust', [
            'business_unit_id' => $product->business_unit_id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => 'adjustment_out',
            'quantity' => 5,
        ])->assertOk()->assertJsonPath('data.quantity_on_hand', '80.000');

        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'adjustment_out']);
    }

    public function test_checkout_reserves_stock_and_cancel_releases_it(): void
    {
        $this->seed();
        $order = $this->createPublicOrder(2);
        $stockItem = StockItem::query()->where('product_id', Product::query()->where('slug', 'premium-medjool-dates-1kg')->value('id'))->firstOrFail();

        $this->assertDatabaseHas('stock_reservations', ['order_id' => $order->id, 'quantity' => 2, 'status' => 'reserved']);
        $this->assertSame('2.000', $stockItem->refresh()->quantity_reserved);

        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());
        $this->postJson("/api/v1/commerce/orders/{$order->id}/cancel", ['note' => 'Customer cancelled.'])->assertOk();

        $this->assertDatabaseHas('stock_reservations', ['order_id' => $order->id, 'status' => 'cancelled']);
        $this->assertSame('0.000', $stockItem->refresh()->quantity_reserved);
    }

    public function test_checkout_fails_when_stock_is_unavailable(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        StockItem::query()->where('product_id', $product->id)->update(['quantity_on_hand' => 1, 'quantity_reserved' => 0]);
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 2])->assertCreated();

        $this->postJson('/api/v1/public/dates/checkout', $this->checkoutPayload($token))->assertStatus(422);
    }

    public function test_public_availability_reports_stock_state(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/dates/products/premium-medjool-dates-1kg/availability')
            ->assertOk()
            ->assertJsonPath('data.inventory_enabled', true)
            ->assertJsonPath('data.in_stock', true);
    }

    private function createPublicOrder(int $quantity): Order
    {
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => $quantity])->assertCreated();
        $orderNumber = $this->postJson('/api/v1/public/dates/checkout', $this->checkoutPayload($token))->assertCreated()->json('data.order_number');

        return Order::query()->where('order_number', $orderNumber)->firstOrFail();
    }

    private function checkoutPayload(string $token): array
    {
        return [
            'session_token' => $token,
            'customer' => ['name' => 'Customer One', 'phone' => '01000000000', 'email' => 'customer@example.com'],
            'shipping_address' => [
                'recipient_name' => 'Customer One',
                'phone' => '01000000000',
                'governorate' => 'Cairo',
                'city' => 'Cairo',
                'street_address' => 'Test street',
            ],
        ];
    }
}
