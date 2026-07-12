<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\RealEstate\Infrastructure\Models\Property;
use App\Modules\RealEstate\Infrastructure\Models\PropertyUnit;
use App\Modules\RealEstate\Infrastructure\Models\RealEstateProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RealEstatePhaseElevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_only_sees_active_real_estate_projects(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();
        RealEstateProject::query()->create(['business_unit_id' => $unit->id, 'name_ar' => 'Active Project', 'slug' => 'active-project', 'project_code' => 'RE-ACT', 'status' => 'active', 'project_type' => 'residential']);
        RealEstateProject::query()->create(['business_unit_id' => $unit->id, 'name_ar' => 'Draft Project', 'slug' => 'draft-project', 'project_code' => 'RE-DRF', 'status' => 'draft', 'project_type' => 'residential']);

        $this->getJson('/api/v1/public/real-estate/real-estate/projects')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'active-project'])
            ->assertJsonMissing(['slug' => 'draft-project']);
    }

    public function test_public_project_detail_hides_private_lead_data(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();
        $project = RealEstateProject::query()->create(['business_unit_id' => $unit->id, 'name_ar' => 'Nile Residence', 'slug' => 'nile-residence', 'project_code' => 'NILE', 'status' => 'active', 'project_type' => 'residential']);

        $this->getJson('/api/v1/public/real-estate/real-estate/projects/nile-residence')
            ->assertOk()
            ->assertJsonPath('data.slug', $project->slug)
            ->assertJsonMissingPath('data.internal_notes')
            ->assertJsonMissingPath('data.leads');
    }

    public function test_real_estate_admin_listing_is_business_unit_scoped(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();
        RealEstateProject::query()->create(['business_unit_id' => $unit->id, 'name_ar' => 'Scoped Project', 'slug' => 'scoped-project', 'project_code' => 'RE-SCP', 'status' => 'active', 'project_type' => 'residential']);

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/real-estate/projects')->assertOk()->assertJsonPath('meta.total', 0);

        Sanctum::actingAs(User::query()->where('email', 'realestate.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/real-estate/projects')->assertOk()->assertJsonFragment(['slug' => 'scoped-project']);
    }

    public function test_public_lead_and_appointment_can_be_submitted(): void
    {
        $this->seed();
        $project = $this->createProjectWithUnit();

        $leadId = $this->postJson('/api/v1/public/real-estate/real-estate/leads', [
            'project_id' => $project->id,
            'source' => 'public_project',
            'name' => 'Buyer One',
            'phone' => '01022223333',
            'email' => 'buyer@example.com',
            'message' => 'Interested in a two-bedroom unit.',
        ])->assertCreated()->assertJsonPath('data.status', 'new')->json('data.id');

        $this->postJson('/api/v1/public/real-estate/real-estate/viewing-requests', [
            'lead_id' => $leadId,
            'project_id' => $project->id,
            'scheduled_at' => now()->addDay()->toISOString(),
            'duration_minutes' => 45,
        ])->assertCreated()->assertJsonPath('data.status', 'scheduled');
    }

    public function test_reservation_rejects_already_reserved_unit(): void
    {
        $this->seed();
        $project = $this->createProjectWithUnit();
        $unit = PropertyUnit::query()->where('project_id', $project->id)->firstOrFail();

        $this->postJson('/api/v1/public/real-estate/real-estate/reservation-interests', [
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'name' => 'Buyer One',
            'phone' => '01022223333',
        ])->assertCreated();

        $this->postJson('/api/v1/public/real-estate/real-estate/reservation-interests', [
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'name' => 'Buyer Two',
            'phone' => '01044445555',
        ])->assertStatus(409);
    }

    public function test_cross_business_unit_unit_is_rejected_for_public_lead(): void
    {
        $this->seed();
        $realEstate = $this->createProjectWithUnit();
        $otherUnit = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        $otherProject = RealEstateProject::query()->create(['business_unit_id' => $otherUnit->id, 'name_ar' => 'Wrong Project', 'slug' => 'wrong-project', 'project_code' => 'WRONG', 'status' => 'active', 'project_type' => 'residential']);
        $wrongProperty = Property::query()->create(['business_unit_id' => $otherUnit->id, 'real_estate_project_id' => $otherProject->id, 'title' => 'Wrong Building', 'name' => 'Wrong Building', 'code' => 'WRONG-B', 'type' => 'building', 'property_type' => 'building', 'status' => 'active']);
        $wrongUnit = PropertyUnit::query()->create(['business_unit_id' => $otherUnit->id, 'project_id' => $otherProject->id, 'property_id' => $wrongProperty->id, 'unit_code' => 'WRONG-1', 'unit_number' => 'WRONG-1', 'unit_type' => 'apartment', 'status' => 'available', 'area' => 100, 'price' => 1000000, 'currency' => 'EGP']);

        $this->postJson('/api/v1/public/real-estate/real-estate/leads', [
            'project_id' => $realEstate->id,
            'unit_id' => $wrongUnit->id,
            'source' => 'public_unit',
            'name' => 'Buyer One',
            'phone' => '01022223333',
        ])->assertStatus(422);
    }

    private function createProjectWithUnit(): RealEstateProject
    {
        $unit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();
        $project = RealEstateProject::query()->create(['business_unit_id' => $unit->id, 'name_ar' => 'Nile Residence', 'slug' => 'nile-residence', 'project_code' => 'NILE', 'status' => 'active', 'project_type' => 'residential']);
        $property = Property::query()->create(['business_unit_id' => $unit->id, 'real_estate_project_id' => $project->id, 'title' => 'Nile Tower A', 'name' => 'Nile Tower A', 'code' => 'NILE-A', 'type' => 'building', 'property_type' => 'building', 'status' => 'active']);
        PropertyUnit::query()->create(['business_unit_id' => $unit->id, 'project_id' => $project->id, 'property_id' => $property->id, 'unit_code' => 'NILE-A-101', 'unit_number' => 'NILE-A-101', 'unit_type' => 'apartment', 'status' => 'available', 'floor' => 1, 'bedrooms' => 2, 'bathrooms' => 2, 'area' => 120, 'price' => 1800000, 'currency' => 'EGP']);

        return $project;
    }
}
