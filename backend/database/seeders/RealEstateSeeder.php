<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\RealEstate\Infrastructure\Models\InstallmentPlan;
use App\Modules\RealEstate\Infrastructure\Models\Property;
use App\Modules\RealEstate\Infrastructure\Models\PropertyUnit;
use App\Modules\RealEstate\Infrastructure\Models\RealEstateProject;
use Illuminate\Database\Seeder;

class RealEstateSeeder extends Seeder
{
    public function run(): void
    {
        $unit = BusinessUnit::query()->where('slug', 'real-estate')->firstOrFail();

        $project = RealEstateProject::query()->updateOrCreate(
            ['business_unit_id' => $unit->id, 'slug' => 'nile-residence'],
            [
                'name' => 'Nile Residence',
                'name_ar' => 'Nile Residence',
                'name_en' => 'Nile Residence',
                'project_code' => 'NILE',
                'status' => 'active',
                'project_type' => 'residential',
                'developer_name' => 'Abu Qasaa Real Estate',
                'description_en' => 'Local development sample residential project.',
                'city' => 'Cairo',
                'governorate' => 'Cairo',
                'starting_price' => 1800000,
                'currency' => 'EGP',
                'is_featured' => true,
                'amenities_json' => ['parking', 'security', 'green_areas'],
            ],
        );

        $property = Property::query()->updateOrCreate(
            ['business_unit_id' => $unit->id, 'code' => 'NILE-A'],
            [
                'real_estate_project_id' => $project->id,
                'title' => 'Nile Tower A',
                'name' => 'Nile Tower A',
                'type' => 'building',
                'property_type' => 'building',
                'status' => 'active',
                'floors_count' => 12,
            ],
        );

        foreach ([['NILE-A-101', 1, 2, 120, 1800000], ['NILE-A-202', 2, 3, 155, 2400000]] as [$code, $floor, $bedrooms, $area, $price]) {
            PropertyUnit::query()->updateOrCreate(
                ['business_unit_id' => $unit->id, 'unit_code' => $code],
                [
                    'project_id' => $project->id,
                    'property_id' => $property->id,
                    'unit_number' => $code,
                    'unit_type' => 'apartment',
                    'status' => 'available',
                    'floor' => $floor,
                    'bedrooms' => $bedrooms,
                    'bathrooms' => 2,
                    'area' => $area,
                    'price' => $price,
                    'currency' => 'EGP',
                    'down_payment' => $price * 0.1,
                    'installment_months' => 84,
                    'is_featured' => $floor === 1,
                ],
            );
        }

        InstallmentPlan::query()->updateOrCreate(
            ['business_unit_id' => $unit->id, 'project_id' => $project->id, 'name' => 'Standard 7 Year Plan'],
            ['down_payment' => 180000, 'installment_count' => 84, 'frequency' => 'monthly', 'installment_amount' => 19285, 'currency' => 'EGP', 'is_active' => true],
        );
    }
}
