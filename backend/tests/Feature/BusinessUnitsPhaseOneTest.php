<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityModule;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessUnitsPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_seeded_business_units(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();

        $this->getJson('/api/v1/business-units')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 4)
            ->assertJsonFragment(['slug' => 'oils'])
            ->assertJsonFragment(['slug' => 'dates']);
    }

    public function test_it_creates_a_business_unit_from_a_template(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();

        $this->postJson('/api/v1/business-units', [
            'name_ar' => 'وحدة اختبار',
            'name_en' => 'Test Unit',
            'slug' => 'test-unit',
            'type' => 'product_store',
            'status' => 'draft',
            'template_key' => 'product_store',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'test-unit');

        $businessUnit = BusinessUnit::query()->where('slug', 'test-unit')->firstOrFail();

        $this->assertGreaterThan(0, $businessUnit->moduleAssignments()->where('is_enabled', true)->count());
        $this->assertDatabaseHas('business_unit_settings', [
            'business_unit_id' => $businessUnit->id,
            'key' => 'checkout_enabled',
        ]);
    }

    public function test_it_updates_a_business_unit(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();
        $businessUnit = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();

        $this->patchJson("/api/v1/business-units/{$businessUnit->id}", [
            'name_ar' => 'غصون للتمور الفاخرة',
            'status' => 'inactive',
        ])
            ->assertOk()
            ->assertJsonPath('data.name_ar', 'غصون للتمور الفاخرة')
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_it_enables_and_disables_modules(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();
        $businessUnit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();

        $this->putJson("/api/v1/business-units/{$businessUnit->id}/modules", [
            'modules' => [
                ['key' => 'cms', 'is_enabled' => true],
                ['key' => 'services', 'is_enabled' => true],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('business_unit_modules', [
            'business_unit_id' => $businessUnit->id,
            'activity_module_id' => ActivityModule::query()->where('key', 'cms')->value('id'),
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('business_unit_modules', [
            'business_unit_id' => $businessUnit->id,
            'activity_module_id' => ActivityModule::query()->where('key', 'rfq')->value('id'),
            'is_enabled' => false,
        ]);
    }

    public function test_it_updates_business_unit_settings(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();
        $businessUnit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();

        $this->putJson("/api/v1/business-units/{$businessUnit->id}/settings", [
            'settings' => [
                'appointments_enabled' => ['value' => false, 'type' => 'boolean', 'group' => 'features'],
                'show_prices' => true,
            ],
        ])
            ->assertOk()
            ->assertJsonFragment(['key' => 'appointments_enabled']);

        $this->assertDatabaseHas('business_unit_settings', [
            'business_unit_id' => $businessUnit->id,
            'key' => 'appointments_enabled',
            'value' => 'false',
        ]);
    }

    public function test_it_reads_public_business_unit_by_slug(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/business-units/oils')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'oils')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_it_updates_feature_flags(): void
    {
        $this->seed();
        $this->actingAsSuperAdmin();
        $featureFlag = FeatureFlag::query()->where('key', 'maintenance_mode')->firstOrFail();

        $this->putJson("/api/v1/feature-flags/{$featureFlag->id}", [
            'value' => true,
            'description' => 'Temporarily enabled for testing.',
        ])
            ->assertOk()
            ->assertJsonPath('data.value', true);

        $this->assertTrue($featureFlag->refresh()->value);
    }

    private function actingAsSuperAdmin(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());
    }
}
