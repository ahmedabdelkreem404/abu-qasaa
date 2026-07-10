<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Identity\Infrastructure\Models\Permission;
use App\Modules\Identity\Infrastructure\Models\Role;
use App\Modules\Identity\Infrastructure\Models\UserBusinessUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccessControlSeeder extends Seeder
{
    private const ROLES = [
        'super_admin' => ['Super Admin', true],
        'business_unit_admin' => ['Business Unit Admin', false],
        'manager' => ['Manager', false],
        'sales' => ['Sales', false],
        'inventory_manager' => ['Inventory Manager', false],
        'orders_manager' => ['Orders Manager', false],
        'content_manager' => ['Content Manager', false],
        'accountant' => ['Accountant', false],
        'support' => ['Support', false],
    ];

    private const PERMISSIONS = [
        'business_units.view', 'business_units.create', 'business_units.update', 'business_units.archive',
        'business_units.manage_modules', 'business_units.manage_settings',
        'users.view', 'users.create', 'users.update', 'users.disable', 'users.assign_roles',
        'products.view', 'products.create', 'products.update', 'products.delete',
        'customers.view', 'customers.create', 'customers.update',
        'orders.view', 'orders.update_status', 'orders.manage',
        'wholesale.view', 'wholesale.manage', 'wholesale.review_applications', 'wholesale.assign_price_lists',
        'payments.view', 'payments.review_manual', 'payments.manage', 'payments.manage_methods', 'payments.refund',
        'inventory.view', 'inventory.manage', 'inventory.adjust', 'inventory.transfer',
        'branches.view', 'branches.manage', 'warehouses.view', 'warehouses.manage',
        'cms.view', 'cms.manage',
        'rfq.view', 'rfq.manage',
        'real_estate.view', 'real_estate.manage',
        'leads.view', 'leads.manage',
        'appointments.view', 'appointments.manage',
        'reports.view',
        'audit_logs.view',
        'settings.view', 'settings.manage',
    ];

    private const ROLE_PERMISSIONS = [
        'business_unit_admin' => [
            'business_units.view', 'business_units.update', 'business_units.manage_modules', 'business_units.manage_settings',
            'users.view', 'users.create', 'users.update', 'users.assign_roles',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'customers.view', 'customers.create', 'customers.update',
            'orders.view', 'orders.update_status', 'orders.manage',
            'wholesale.view', 'wholesale.manage', 'wholesale.review_applications', 'wholesale.assign_price_lists',
            'payments.view', 'payments.review_manual', 'payments.manage', 'payments.manage_methods',
            'inventory.view', 'inventory.manage', 'inventory.adjust', 'inventory.transfer',
            'branches.view', 'branches.manage', 'warehouses.view', 'warehouses.manage',
            'cms.view', 'cms.manage',
            'rfq.view', 'rfq.manage',
            'real_estate.view', 'real_estate.manage',
            'leads.view', 'leads.manage',
            'appointments.view', 'appointments.manage',
            'reports.view', 'audit_logs.view',
            'settings.view', 'settings.manage',
        ],
        'manager' => ['business_units.view', 'users.view', 'products.view', 'customers.view', 'orders.view', 'wholesale.view', 'payments.view', 'inventory.view', 'branches.view', 'warehouses.view', 'cms.view', 'reports.view', 'settings.view'],
        'sales' => ['business_units.view', 'products.view', 'customers.view', 'customers.create', 'orders.view', 'orders.update_status', 'wholesale.view', 'leads.view', 'leads.manage'],
        'inventory_manager' => ['business_units.view', 'products.view', 'inventory.view', 'inventory.manage', 'inventory.adjust', 'inventory.transfer', 'branches.view', 'branches.manage', 'warehouses.view', 'warehouses.manage'],
        'orders_manager' => ['business_units.view', 'orders.view', 'orders.update_status', 'orders.manage'],
        'content_manager' => ['business_units.view', 'cms.view', 'cms.manage'],
        'accountant' => ['business_units.view', 'payments.view', 'payments.review_manual', 'payments.refund', 'reports.view'],
        'support' => ['business_units.view', 'orders.view', 'leads.view', 'appointments.view'],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $key => [$name, $isGlobal]) {
            Role::query()->updateOrCreate(['key' => $key], ['name' => $name, 'is_global' => $isGlobal]);
        }

        foreach (self::PERMISSIONS as $permission) {
            Permission::query()->updateOrCreate(
                ['key' => $permission],
                ['name' => str($permission)->replace('.', ' ')->title()->toString(), 'group' => str($permission)->before('.')->toString()],
            );
        }

        $allPermissionIds = Permission::query()->pluck('id');
        Role::query()->where('key', 'super_admin')->firstOrFail()->permissions()->sync($allPermissionIds);

        foreach (self::ROLE_PERMISSIONS as $roleKey => $permissions) {
            $role = Role::query()->where('key', $roleKey)->firstOrFail();
            $role->permissions()->sync(Permission::query()->whereIn('key', $permissions)->pluck('id'));
        }

        $superAdmin = User::query()->updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@abuqasaa.test')],
            [
                'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
                'status' => 'active',
            ],
        );
        $superAdmin->roles()->sync([Role::query()->where('key', 'super_admin')->value('id')]);

        $this->seedDemoAdmin('oils.admin@abuqasaa.test', 'oils');
        $this->seedDemoAdmin('dates.admin@abuqasaa.test', 'dates');
        $this->seedDemoAdmin('realestate.admin@abuqasaa.test', 'real-estate');
        $this->seedDemoAdmin('importexport.admin@abuqasaa.test', 'import-export');
    }

    private function seedDemoAdmin(string $email, string $businessUnitSlug): void
    {
        $role = Role::query()->where('key', 'business_unit_admin')->firstOrFail();
        $businessUnit = BusinessUnit::query()->where('slug', $businessUnitSlug)->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $businessUnit->name_en.' Admin',
                'password' => Hash::make(env('DEMO_USER_PASSWORD', 'password')),
                'status' => 'active',
            ],
        );

        UserBusinessUnit::query()->updateOrCreate(
            ['user_id' => $user->id, 'business_unit_id' => $businessUnit->id],
            [
                'role_id' => $role->id,
                'role_key' => $role->key,
                'is_active' => true,
            ],
        );
    }
}
