<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Commerce\Domain\Enums\WholesaleApplicationStatus;
use App\Modules\Commerce\Domain\Enums\WholesaleCustomerStatus;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use Illuminate\Database\Seeder;

class WholesaleSeeder extends Seeder
{
    public function run(): void
    {
        $oils = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        $wholesale = PriceList::query()->where('business_unit_id', $oils->id)->where('key', 'wholesale')->firstOrFail();

        Customer::query()->updateOrCreate(
            ['business_unit_id' => $oils->id, 'phone' => '01011111111'],
            [
                'type' => 'shop',
                'name' => 'Demo Wholesale Customer',
                'company_name' => 'Demo Oils Shop',
                'approval_status' => 'approved',
                'wholesale_status' => WholesaleCustomerStatus::Approved->value,
                'price_list_id' => $wholesale->id,
                'approved_at' => now(),
                'notes' => 'Seeded demo wholesale customer for Phase 9.',
            ],
        );

        WholesaleApplication::query()->updateOrCreate(
            ['business_unit_id' => $oils->id, 'phone' => '01022222222'],
            [
                'status' => WholesaleApplicationStatus::Pending->value,
                'applicant_name' => 'Pending Wholesale Applicant',
                'company_name' => 'Pending Oils Shop',
                'shop_name' => 'Pending Oils Shop',
                'governorate' => 'Cairo',
                'city' => 'Nasr City',
                'message' => 'Interested in wholesale oils pricing.',
            ],
        );
    }
}
