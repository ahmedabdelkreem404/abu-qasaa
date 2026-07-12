<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\ServicesRfq\Infrastructure\Models\Service;
use Illuminate\Database\Seeder;

class ServicesRfqSeeder extends Seeder
{
    public function run(): void
    {
        $unit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();

        foreach ([['Sea Freight', 'sea-freight', 'shipping'], ['Air Freight', 'air-freight', 'shipping'], ['Customs Clearance', 'customs-clearance', 'clearance']] as $index => [$name, $slug, $category]) {
            Service::query()->updateOrCreate(
                ['business_unit_id' => $unit->id, 'slug' => $slug],
                [
                    'name' => $name,
                    'name_ar' => $name,
                    'name_en' => $name,
                    'category' => $category,
                    'summary_en' => 'Local development sample import/export service.',
                    'description' => 'Seeded service for RFQ workflows.',
                    'description_en' => 'Seeded service for RFQ workflows.',
                    'status' => 'published',
                    'is_featured' => $index === 0,
                    'sort_order' => $index,
                ],
            );
        }
    }
}
