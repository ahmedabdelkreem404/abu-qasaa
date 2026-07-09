<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogPhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_catalog_dashboard_endpoints(): void
    {
        $this->seed();

        $this->getJson('/api/v1/catalog/products')->assertUnauthorized();
    }

    public function test_super_admin_can_create_category_brand_and_product(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $categoryId = $this->postJson('/api/v1/catalog/categories', [
            'business_unit_id' => $unit->id,
            'name_ar' => 'Test Category',
            'slug' => 'test-category',
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $brandId = $this->postJson('/api/v1/catalog/brands', [
            'business_unit_id' => $unit->id,
            'name_ar' => 'Test Brand',
            'slug' => 'test-brand',
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/catalog/products', [
            'business_unit_id' => $unit->id,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'name_ar' => 'Test Product',
            'slug' => 'test-product',
            'product_type' => 'simple',
            'status' => 'draft',
            'visibility' => 'public',
        ])->assertCreated()->assertJsonPath('data.slug', 'test-product');
    }

    public function test_business_unit_admin_can_create_product_for_assigned_business_unit(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->postJson('/api/v1/catalog/products', [
            'business_unit_id' => $unit->id,
            'name_ar' => 'Assigned Product',
            'slug' => 'assigned-product',
            'product_type' => 'simple',
            'status' => 'draft',
            'visibility' => 'public',
        ])->assertCreated();
    }

    public function test_business_unit_admin_cannot_create_product_for_unassigned_business_unit(): void
    {
        $this->seed();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->postJson('/api/v1/catalog/products', [
            'business_unit_id' => $dates->id,
            'name_ar' => 'Blocked Product',
            'slug' => 'blocked-product',
            'product_type' => 'simple',
            'status' => 'draft',
            'visibility' => 'public',
        ])->assertForbidden();
    }

    public function test_product_cannot_be_created_when_products_module_is_disabled(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->postJson('/api/v1/catalog/products', [
            'business_unit_id' => $unit->id,
            'name_ar' => 'No Module Product',
            'slug' => 'no-module-product',
            'product_type' => 'simple',
            'status' => 'draft',
            'visibility' => 'public',
        ])->assertForbidden();
    }

    public function test_category_parent_must_belong_to_same_business_unit(): void
    {
        $this->seed();
        $oils = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        $datesCategory = Category::query()->where('business_unit_id', $dates->id)->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->postJson('/api/v1/catalog/categories', [
            'business_unit_id' => $oils->id,
            'parent_id' => $datesCategory->id,
            'name_ar' => 'Invalid Child',
            'slug' => 'invalid-child',
            'status' => 'active',
        ])->assertStatus(422);
    }

    public function test_brand_category_and_product_listing_is_business_unit_scoped(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/catalog/categories')->assertOk()->assertJsonPath('meta.total', 3);
        $this->getJson('/api/v1/catalog/brands')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/catalog/products')->assertOk()->assertJsonPath('meta.total', 3);
    }

    public function test_public_can_list_published_products_by_business_slug(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/oils/products')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonMissingPath('data.0.cost_price');
    }

    public function test_public_cannot_see_draft_products(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        Product::query()->create([
            'business_unit_id' => $unit->id,
            'name_ar' => 'Draft Product',
            'slug' => 'draft-product',
            'product_type' => 'simple',
            'status' => 'draft',
            'visibility' => 'public',
        ]);

        $this->getJson('/api/v1/public/oils/products')->assertOk()->assertJsonMissing(['slug' => 'draft-product']);
    }

    public function test_public_product_detail_works_by_slug_and_hides_cost_price(): void
    {
        $this->seed();
        Product::query()->where('slug', 'premium-engine-oil-4l')->update(['cost_price' => 200]);

        $this->getJson('/api/v1/public/oils/products/premium-engine-oil-4l')
            ->assertOk()
            ->assertJsonPath('data.slug', 'premium-engine-oil-4l')
            ->assertJsonMissingPath('data.cost_price');
    }

    public function test_price_list_and_product_price_can_be_created_for_same_business_unit(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        $product = Product::query()->where('business_unit_id', $unit->id)->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $priceListId = $this->postJson('/api/v1/catalog/price-lists', [
            'business_unit_id' => $unit->id,
            'name' => 'Special',
            'key' => 'special-local',
            'type' => 'special',
        ])->assertCreated()->json('data.id');

        $this->putJson("/api/v1/catalog/products/{$product->id}/prices", [
            'prices' => [
                ['price_list_id' => $priceListId, 'min_quantity' => 1, 'price' => 399],
            ],
        ])->assertOk()->assertJsonPath('data.prices.0.price', '399.00');
    }

    public function test_product_price_rejects_mismatched_business_unit_price_list(): void
    {
        $this->seed();
        $oilsProduct = Product::query()->whereHas('businessUnit', fn ($query) => $query->where('slug', 'oils'))->firstOrFail();
        $datesPriceList = PriceList::query()->whereHas('businessUnit', fn ($query) => $query->where('slug', 'dates'))->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->putJson("/api/v1/catalog/products/{$oilsProduct->id}/prices", [
            'prices' => [
                ['price_list_id' => $datesPriceList->id, 'min_quantity' => 1, 'price' => 399],
            ],
        ])->assertStatus(422);
    }
}
