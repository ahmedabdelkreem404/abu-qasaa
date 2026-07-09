<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\ActivityModule;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityTemplate;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitModule;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\BusinessUnits\Infrastructure\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class BusinessUnitFoundationSeeder extends Seeder
{
    private const MODULES = [
        ['key' => 'cms', 'name' => 'CMS', 'category' => 'content'],
        ['key' => 'products', 'name' => 'Products', 'category' => 'catalog'],
        ['key' => 'categories', 'name' => 'Categories', 'category' => 'catalog'],
        ['key' => 'brands', 'name' => 'Brands', 'category' => 'catalog'],
        ['key' => 'orders', 'name' => 'Orders', 'category' => 'commerce'],
        ['key' => 'payments', 'name' => 'Payments', 'category' => 'payments'],
        ['key' => 'manual_payments', 'name' => 'Manual Payments', 'category' => 'payments'],
        ['key' => 'paymob', 'name' => 'Paymob', 'category' => 'payments'],
        ['key' => 'inventory', 'name' => 'Inventory', 'category' => 'inventory'],
        ['key' => 'branches', 'name' => 'Branches', 'category' => 'operations'],
        ['key' => 'warehouses', 'name' => 'Warehouses', 'category' => 'inventory'],
        ['key' => 'wholesale', 'name' => 'Wholesale', 'category' => 'commerce'],
        ['key' => 'price_lists', 'name' => 'Price Lists', 'category' => 'commerce'],
        ['key' => 'customers', 'name' => 'Customers', 'category' => 'identity'],
        ['key' => 'services', 'name' => 'Services', 'category' => 'services'],
        ['key' => 'rfq', 'name' => 'RFQ', 'category' => 'services'],
        ['key' => 'documents', 'name' => 'Documents', 'category' => 'services'],
        ['key' => 'real_estate_projects', 'name' => 'Real Estate Projects', 'category' => 'real_estate'],
        ['key' => 'properties', 'name' => 'Properties', 'category' => 'real_estate'],
        ['key' => 'property_units', 'name' => 'Property Units', 'category' => 'real_estate'],
        ['key' => 'leads', 'name' => 'Leads', 'category' => 'crm'],
        ['key' => 'appointments', 'name' => 'Appointments', 'category' => 'crm'],
        ['key' => 'reports', 'name' => 'Reports', 'category' => 'reports'],
        ['key' => 'audit_logs', 'name' => 'Audit Logs', 'category' => 'audit'],
        ['key' => 'settings', 'name' => 'Settings', 'category' => 'settings'],
    ];

    private const TEMPLATES = [
        [
            'key' => 'product_store',
            'name' => 'Product Store',
            'type' => 'product_store',
            'description' => 'Catalog, ecommerce, payments, and content for retail product businesses.',
            'modules' => ['cms', 'products', 'categories', 'brands', 'orders', 'payments', 'manual_payments', 'paymob', 'inventory', 'branches', 'customers', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => true, 'checkout_enabled' => true, 'show_prices' => true, 'allow_guest_checkout' => true],
        ],
        [
            'key' => 'wholesale_store',
            'name' => 'Wholesale Store',
            'type' => 'wholesale_store',
            'description' => 'Product catalog, wholesale pricing, inventory, and account-oriented ordering.',
            'modules' => ['cms', 'products', 'categories', 'brands', 'orders', 'payments', 'manual_payments', 'paymob', 'inventory', 'branches', 'warehouses', 'wholesale', 'price_lists', 'customers', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => true, 'checkout_enabled' => true, 'show_prices' => true, 'allow_guest_checkout' => false, 'wholesale_enabled' => true],
        ],
        [
            'key' => 'services_rfq',
            'name' => 'Services/RFQ',
            'type' => 'services_rfq',
            'description' => 'Services, document collection, leads, and request-for-quotation workflows.',
            'modules' => ['cms', 'services', 'rfq', 'documents', 'leads', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false, 'rfq_enabled' => true],
        ],
        [
            'key' => 'real_estate',
            'name' => 'Real Estate',
            'type' => 'real_estate',
            'description' => 'Projects, properties, units, leads, appointments, and content.',
            'modules' => ['cms', 'real_estate_projects', 'properties', 'property_units', 'leads', 'appointments', 'manual_payments', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false, 'appointments_enabled' => true],
        ],
        [
            'key' => 'content_only',
            'name' => 'Content Only',
            'type' => 'content_only',
            'description' => 'CMS and lead capture for informational business units.',
            'modules' => ['cms', 'leads', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false],
        ],
        [
            'key' => 'hybrid',
            'name' => 'Hybrid',
            'type' => 'hybrid',
            'description' => 'Configurable mixed activity template for future business models.',
            'modules' => ['cms', 'products', 'services', 'leads', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false],
        ],
    ];

    private const BUSINESS_UNITS = [
        [
            'name_ar' => 'أبناء أبو قاعود للزيوت ومواد التشحيم',
            'name_en' => 'Abnaa Abu Qasaa Oils & Lubricants',
            'slug' => 'oils',
            'type' => 'wholesale_store',
            'modules' => ['cms', 'products', 'categories', 'brands', 'orders', 'payments', 'manual_payments', 'paymob', 'inventory', 'branches', 'warehouses', 'wholesale', 'price_lists', 'customers', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => true, 'checkout_enabled' => true, 'show_prices' => true, 'allow_guest_checkout' => false, 'manual_payment_enabled' => true, 'paymob_enabled' => true, 'inventory_enabled' => true, 'wholesale_enabled' => true],
        ],
        [
            'name_ar' => 'غصون للتمور',
            'name_en' => 'Ghosoun Dates',
            'slug' => 'dates',
            'type' => 'product_store',
            'modules' => ['cms', 'products', 'categories', 'brands', 'orders', 'payments', 'manual_payments', 'paymob', 'inventory', 'branches', 'customers', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => true, 'checkout_enabled' => true, 'show_prices' => true, 'allow_guest_checkout' => true, 'manual_payment_enabled' => true, 'paymob_enabled' => true, 'inventory_enabled' => true, 'wholesale_enabled' => false],
        ],
        [
            'name_ar' => 'أبناء أبو قاعود للاستيراد والتصدير',
            'name_en' => 'Abnaa Abu Qasaa Import & Export',
            'slug' => 'import-export',
            'type' => 'services_rfq',
            'modules' => ['cms', 'services', 'rfq', 'documents', 'leads', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false, 'rfq_enabled' => true, 'manual_payment_enabled' => true, 'paymob_enabled' => false],
        ],
        [
            'name_ar' => 'أبناء أبو قاعود للعقارات',
            'name_en' => 'Abnaa Abu Qasaa Real Estate',
            'slug' => 'real-estate',
            'type' => 'real_estate',
            'modules' => ['cms', 'real_estate_projects', 'properties', 'property_units', 'leads', 'appointments', 'manual_payments', 'reports', 'audit_logs', 'settings'],
            'settings' => ['registration_enabled' => false, 'checkout_enabled' => false, 'show_prices' => false, 'appointments_enabled' => true, 'manual_payment_enabled' => true, 'paymob_enabled' => false],
        ],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $module) {
            ActivityModule::query()->updateOrCreate(
                ['key' => $module['key']],
                ['name' => $module['name'], 'category' => $module['category'], 'is_active' => true],
            );
        }

        foreach (self::TEMPLATES as $template) {
            ActivityTemplate::query()->updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'type' => $template['type'],
                    'default_modules_json' => $template['modules'],
                    'default_settings_json' => $template['settings'],
                    'is_active' => true,
                ],
            );
        }

        foreach ([
            'global_registration_enabled' => true,
            'global_checkout_enabled' => true,
            'maintenance_mode' => false,
        ] as $key => $value) {
            FeatureFlag::query()->updateOrCreate(
                ['business_unit_id' => null, 'key' => $key],
                ['value' => $value, 'description' => 'Global platform flag.'],
            );
        }

        foreach (self::BUSINESS_UNITS as $businessUnitData) {
            $businessUnit = BusinessUnit::query()->updateOrCreate(
                ['slug' => $businessUnitData['slug']],
                [
                    'name_ar' => $businessUnitData['name_ar'],
                    'name_en' => $businessUnitData['name_en'],
                    'type' => $businessUnitData['type'],
                    'status' => 'active',
                ],
            );

            $modules = ActivityModule::query()->whereIn('key', $businessUnitData['modules'])->get();

            foreach ($modules as $module) {
                BusinessUnitModule::query()->updateOrCreate(
                    ['business_unit_id' => $businessUnit->id, 'activity_module_id' => $module->id],
                    ['is_enabled' => true],
                );
            }

            foreach ($businessUnitData['settings'] as $key => $value) {
                BusinessUnitSetting::query()->updateOrCreate(
                    ['business_unit_id' => $businessUnit->id, 'key' => $key],
                    ['value' => $value, 'type' => 'boolean', 'group' => 'features'],
                );
            }
        }
    }
}
