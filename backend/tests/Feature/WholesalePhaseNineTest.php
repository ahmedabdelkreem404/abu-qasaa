<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WholesalePhaseNineTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_submit_wholesale_application_when_enabled(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/oils/wholesale/apply', $this->applicationPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonMissingPath('data.admin_rejection_reason');

        $this->assertDatabaseHas('wholesale_applications', ['business_unit_id' => $this->oils()->id, 'phone' => '01033333333']);
    }

    public function test_public_cannot_submit_wholesale_application_when_disabled(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/dates/wholesale/apply', $this->applicationPayload())->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_access_wholesale_dashboard(): void
    {
        $this->seed();

        $this->getJson('/api/v1/wholesale/applications')->assertUnauthorized();
    }

    public function test_super_admin_and_assigned_admin_can_list_applications(): void
    {
        $this->seed();

        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/wholesale/applications')->assertOk()->assertJsonPath('meta.total', 1);

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/wholesale/applications')->assertOk()->assertJsonPath('meta.total', 1);

        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/wholesale/applications')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/wholesale/applications/'.WholesaleApplication::query()->firstOrFail()->id)->assertForbidden();
    }

    public function test_authorized_admin_can_approve_application_and_create_customer(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $application = WholesaleApplication::query()->where('status', 'pending')->firstOrFail();
        $priceList = PriceList::query()->where('business_unit_id', $this->oils()->id)->where('key', 'wholesale')->firstOrFail();

        $this->postJson("/api/v1/wholesale/applications/{$application->id}/approve", ['price_list_id' => $priceList->id])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('customers', [
            'business_unit_id' => $this->oils()->id,
            'phone' => $application->phone,
            'wholesale_status' => 'approved',
            'price_list_id' => $priceList->id,
        ]);
    }

    public function test_authorized_admin_can_reject_application(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $application = WholesaleApplication::query()->where('status', 'pending')->firstOrFail();

        $this->postJson("/api/v1/wholesale/applications/{$application->id}/reject", ['rejection_reason' => 'Incomplete documents.'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');
    }

    public function test_approved_customer_can_access_wholesale_pricing_and_unapproved_customer_cannot(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/oils/wholesale/products')->assertForbidden();

        $token = $this->postJson('/api/v1/public/oils/wholesale/access', ['phone' => '01011111111'])
            ->assertOk()
            ->json('data.token');

        $this->getJson("/api/v1/public/oils/wholesale/products?phone=01011111111&token={$token}")
            ->assertOk()
            ->assertJsonPath('data.0.price_audience', 'wholesale')
            ->assertJsonPath('data.0.min_quantity_applied', 12);
    }

    public function test_wholesale_price_resolves_from_assigned_price_list_and_min_quantity_is_enforced_in_cart(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-engine-oil-4l')->firstOrFail();
        $token = $this->postJson('/api/v1/public/oils/cart')->json('data.session_token');
        $access = $this->postJson('/api/v1/public/oils/wholesale/access', ['phone' => '01011111111'])->json('data.token');

        $this->postJson("/api/v1/public/oils/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
            'wholesale_phone' => '01011111111',
            'wholesale_token' => $access,
        ])->assertStatus(422);

        $this->postJson("/api/v1/public/oils/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 12,
            'wholesale_phone' => '01011111111',
            'wholesale_token' => $access,
        ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.unit_price', '360.00')
            ->assertJsonPath('data.items.0.metadata_json.price_audience', 'wholesale');
    }

    public function test_retail_cart_still_uses_retail_price(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');

        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])
            ->assertCreated()
            ->assertJsonPath('data.items.0.unit_price', '280.00')
            ->assertJsonPath('data.items.0.metadata_json.price_audience', 'retail');
    }

    public function test_wholesale_order_items_preserve_price_list_metadata(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-engine-oil-4l')->firstOrFail();
        $token = $this->postJson('/api/v1/public/oils/cart')->json('data.session_token');
        $access = $this->postJson('/api/v1/public/oils/wholesale/access', ['phone' => '01011111111'])->json('data.token');
        $this->postJson("/api/v1/public/oils/cart/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 12,
            'wholesale_phone' => '01011111111',
            'wholesale_token' => $access,
        ])->assertCreated();

        $this->postJson('/api/v1/public/oils/checkout', [
            ...$this->checkoutPayload($token),
            'wholesale_phone' => '01011111111',
            'wholesale_token' => $access,
        ])
            ->assertCreated()
            ->assertJsonPath('data.source', 'public_wholesale_checkout')
            ->assertJsonPath('data.items.0.metadata_json.price_audience', 'wholesale');
    }

    public function test_assigning_price_list_from_another_business_unit_is_rejected(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $customer = Customer::query()->where('phone', '01011111111')->firstOrFail();
        $datesPriceList = PriceList::query()->where('business_unit_id', $this->dates()->id)->firstOrFail();

        $this->postJson("/api/v1/wholesale/customers/{$customer->id}/assign-price-list", ['price_list_id' => $datesPriceList->id])
            ->assertStatus(422);
    }

    public function test_missing_wholesale_price_is_rejected(): void
    {
        $this->seed();
        $product = Product::query()->where('slug', 'premium-engine-oil-4l')->firstOrFail();
        $product->prices()->whereHas('priceList', fn ($query) => $query->where('type', 'wholesale'))->delete();
        $access = $this->postJson('/api/v1/public/oils/wholesale/access', ['phone' => '01011111111'])->json('data.token');

        $this->getJson("/api/v1/public/oils/wholesale/products/{$product->slug}?phone=01011111111&token={$access}")
            ->assertStatus(422);
    }

    public function test_dates_wholesale_remains_disabled_by_default(): void
    {
        $this->seed();
        $dates = $this->dates();

        $this->assertFalse((bool) BusinessUnitSetting::query()->where('business_unit_id', $dates->id)->where('key', 'wholesale_enabled')->value('value'));
        $this->postJson('/api/v1/public/dates/wholesale/access', ['phone' => '01011111111'])->assertNotFound();
    }

    private function applicationPayload(): array
    {
        return [
            'applicant_name' => 'Wholesale Applicant',
            'phone' => '01033333333',
            'email' => 'applicant@example.com',
            'company_name' => 'Applicant Shop',
            'shop_name' => 'Applicant Shop',
            'governorate' => 'Cairo',
            'city' => 'Cairo',
            'address' => 'Test wholesale address',
            'message' => 'Please review my application.',
        ];
    }

    private function checkoutPayload(string $token): array
    {
        return [
            'session_token' => $token,
            'customer' => ['name' => 'Demo Wholesale Customer', 'phone' => '01011111111', 'email' => 'wholesale@example.com'],
            'shipping_address' => [
                'recipient_name' => 'Demo Wholesale Customer',
                'phone' => '01011111111',
                'governorate' => 'Cairo',
                'city' => 'Cairo',
                'street_address' => 'Wholesale street',
            ],
        ];
    }

    private function oils(): BusinessUnit
    {
        return BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
    }

    private function dates(): BusinessUnit
    {
        return BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
    }
}
