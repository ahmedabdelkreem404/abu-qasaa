<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommercePhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_create_cart_for_active_product_business_unit(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/dates/cart')
            ->assertOk()
            ->assertJsonPath('data.business_unit.slug', 'dates')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_public_can_add_published_product_to_cart_and_totals_are_calculated(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.subtotal', '560.00')
            ->assertJsonPath('data.grand_total', '560.00');
    }

    public function test_public_cannot_add_draft_product_to_cart(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $product->update(['status' => 'draft']);
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertNotFound();
    }

    public function test_public_cannot_mix_products_from_different_business_units(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-engine-oil-4l')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertNotFound();
    }

    public function test_min_and_max_order_quantity_are_respected(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $product->update(['min_order_quantity' => 2, 'max_order_quantity' => 3]);
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])->assertStatus(422);
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 4])->assertStatus(422);
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 2])->assertCreated();
    }

    public function test_checkout_creates_order_from_cart_and_converts_cart(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])->assertCreated();

        $orderNumber = $this->postJson('/api/v1/public/dates/checkout', $this->checkoutPayload($token))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending_review')
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->json('data.order_number');

        $this->assertDatabaseHas('carts', ['session_token' => $token, 'status' => 'converted']);
        $this->assertDatabaseHas('orders', ['order_number' => $orderNumber, 'customer_phone' => '01000000000']);
    }

    public function test_checkout_requires_phone_and_shipping_address(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/dates/checkout', ['session_token' => 'missing'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer', 'shipping_address']);
    }

    public function test_checkout_is_blocked_when_checkout_setting_or_orders_module_is_disabled(): void
    {
        $this->seed();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        BusinessUnitSetting::query()->where('business_unit_id', $dates->id)->where('key', 'checkout_enabled')->update(['value' => false]);
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson('/api/v1/public/dates/checkout', $this->checkoutPayload($token))->assertForbidden();
        $this->postJson('/api/v1/public/import-export/checkout', $this->checkoutPayload('x'))->assertNotFound();
    }

    public function test_public_order_lookup_requires_correct_phone_and_hides_internal_notes(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $order->update(['internal_notes' => 'private']);

        $this->getJson("/api/v1/public/dates/orders/{$order->order_number}?phone=wrong")->assertNotFound();
        $this->getJson("/api/v1/public/dates/orders/{$order->order_number}?phone=01000000000")
            ->assertOk()
            ->assertJsonPath('data.order_number', $order->order_number)
            ->assertJsonMissingPath('data.internal_notes');
    }

    public function test_unauthenticated_user_cannot_access_dashboard_order_endpoints(): void
    {
        $this->seed();

        $this->getJson('/api/v1/commerce/orders')->assertUnauthorized();
    }

    public function test_admin_order_listing_is_business_unit_scoped(): void
    {
        $this->seed();
        $this->createPublicOrder();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/commerce/orders')->assertOk()->assertJsonPath('meta.total', 1);

        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/commerce/orders')->assertOk()->assertJsonPath('meta.total', 1);

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/commerce/orders')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/commerce/orders/'.Order::query()->firstOrFail()->id)->assertForbidden();
    }

    public function test_authorized_user_can_update_order_status_and_history_is_created(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());

        $this->putJson("/api/v1/commerce/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'note' => 'Confirmed by admin.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'from_status' => 'pending_review', 'to_status' => 'confirmed']);
    }

    private function createPublicOrder(): Order
    {
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
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
            'notes' => 'Please confirm before shipping.',
        ];
    }
}
