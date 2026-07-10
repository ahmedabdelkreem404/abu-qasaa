<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BusinessUnitFoundationSeeder::class);
        $this->call(AccessControlSeeder::class);
        $this->call(CmsContentSeeder::class);
        $this->call(CatalogSeeder::class);
        $this->call(InventorySeeder::class);
        $this->call(PaymentSeeder::class);
    }
}
