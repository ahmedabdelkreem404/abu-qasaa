<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductBundle;
use App\Modules\Catalog\Infrastructure\Models\ProductCollection;
use App\Modules\Commerce\Infrastructure\Models\CartItem;
use App\Modules\Commerce\Infrastructure\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogPhaseTenMerchandisingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_collections_and_not_drafts(): void
    {
        $this->seed();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();

        ProductCollection::query()->create([
            'business_unit_id' => $dates->id,
            'name_ar' => 'Test Gift Boxes',
            'slug' => 'test-gift-boxes',
            'status' => 'active',
            'is_featured' => true,
        ]);
        ProductCollection::query()->create([
            'business_unit_id' => $dates->id,
            'name_ar' => 'Draft Collection',
            'slug' => 'draft-collection',
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/public/dates/collections')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'test-gift-boxes'])
            ->assertJsonMissing(['slug' => 'draft-collection']);
    }

    public function test_public_collection_detail_filters_products_to_same_business_unit(): void
    {
        $this->seed();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        $oilsProduct = Product::query()->whereHas('businessUnit', fn ($query) => $query->where('slug', 'oils'))->firstOrFail();
        $datesProduct = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $collection = ProductCollection::query()->create([
            'business_unit_id' => $dates->id,
            'name_ar' => 'Test Premium Dates',
            'slug' => 'test-premium-dates',
            'status' => 'active',
        ]);
        $collection->items()->create(['product_id' => $datesProduct->id]);
        $collection->items()->create(['product_id' => $oilsProduct->id]);

        $this->getJson('/api/v1/public/dates/collections/test-premium-dates')
            ->assertOk()
            ->assertJsonPath('data.slug', 'test-premium-dates')
            ->assertJsonPath('data.products.0.slug', 'premium-medjool-dates-1kg')
            ->assertJsonMissing(['slug' => $oilsProduct->slug])
            ->assertJsonMissingPath('data.products.0.cost_price');
    }

    public function test_public_corporate_gift_inquiry_is_stored_for_business_unit(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/dates/corporate-gift-inquiries', [
            'company_name' => 'Acme Gifts',
            'contact_name' => 'Mona Ali',
            'phone' => '01011112222',
            'email' => 'mona@example.com',
            'quantity' => 50,
            'budget_range' => '10000-20000',
            'occasion' => 'Ramadan',
            'message' => 'Need branded boxes.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.company_name', 'Acme Gifts');

        $this->assertDatabaseHas('corporate_gift_inquiries', [
            'company_name' => 'Acme Gifts',
            'phone' => '01011112222',
            'status' => 'new',
        ]);
    }

    public function test_dashboard_merchandising_is_business_unit_scoped(): void
    {
        $this->seed();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->postJson('/api/v1/catalog/collections', [
            'business_unit_id' => $dates->id,
            'name_ar' => 'Blocked Collection',
            'slug' => 'blocked-collection',
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_bundle_metadata_is_snapshotted_to_cart_and_order_items(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        ProductBundle::query()->create([
            'business_unit_id' => $product->business_unit_id,
            'product_id' => $product->id,
            'name_ar' => 'Premium Medjool Gift Box',
            'bundle_type' => 'fixed_box',
            'pricing_mode' => 'use_parent_product_price',
            'is_active' => true,
        ]);
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $cartItem = CartItem::query()->firstOrFail();
        $this->assertSame('Premium Medjool Gift Box', $cartItem->metadata_json['bundle']['name_ar']);

        $orderNumber = $this->postJson('/api/v1/public/dates/checkout', $this->checkoutPayload($token))->assertCreated()->json('data.order_number');
        $order = Order::query()->where('order_number', $orderNumber)->with('items')->firstOrFail();

        $this->assertSame('Premium Medjool Gift Box', $order->items->first()->metadata_json['bundle']['name_ar']);
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
