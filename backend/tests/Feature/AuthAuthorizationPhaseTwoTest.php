<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Identity\Infrastructure\Models\Role;
use App\Modules\Identity\Infrastructure\Models\UserBusinessUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAuthorizationPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login(): void
    {
        $this->seed();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@abuqasaa.test',
            'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email', 'roles', 'permissions', 'business_units']]]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->seed();
        User::query()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@abuqasaa.test',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@abuqasaa.test',
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_me_and_logout(): void
    {
        $this->seed();
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@abuqasaa.test',
            'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
        ])->json('data.token');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@abuqasaa.test');

        $this->withToken($token)->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_user_cannot_access_protected_business_unit_endpoints(): void
    {
        $this->seed();

        $this->getJson('/api/v1/business-units')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_super_admin_can_list_all_business_units(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/business-units')
            ->assertOk()
            ->assertJsonPath('meta.total', 4);
    }

    public function test_business_unit_admin_can_access_assigned_business_unit(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail();
        $oils = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/business-units/{$oils->id}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'oils');
    }

    public function test_business_unit_admin_cannot_access_unassigned_business_unit(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail();
        $dates = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/business-units/{$dates->id}")
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_manage_modules(): void
    {
        $this->seed();
        $oils = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        $supportRole = Role::query()->where('key', 'support')->firstOrFail();
        $user = User::query()->create([
            'name' => 'Support User',
            'email' => 'support@abuqasaa.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        UserBusinessUnit::query()->create([
            'user_id' => $user->id,
            'business_unit_id' => $oils->id,
            'role_id' => $supportRole->id,
            'role_key' => $supportRole->key,
            'is_active' => true,
        ]);
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/business-units/{$oils->id}/modules", [
            'modules' => [['key' => 'cms', 'is_enabled' => true]],
        ])->assertForbidden();
    }

    public function test_public_business_unit_endpoints_still_work_without_auth(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/business-units')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_assigned_user_sees_only_allowed_business_units_and_super_admin_bypasses_scope(): void
    {
        $this->seed();
        $oilsAdmin = User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail();
        Sanctum::actingAs($oilsAdmin);

        $this->getJson('/api/v1/business-units')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'oils');

        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/business-units')
            ->assertOk()
            ->assertJsonPath('meta.total', 4);
    }
}
