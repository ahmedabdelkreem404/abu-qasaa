<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Inventory\Domain\Enums\StockMovementReason;
use App\Modules\Inventory\Domain\Enums\StockMovementType;
use App\Modules\Inventory\Infrastructure\Models\Branch;
use App\Modules\Inventory\Infrastructure\Models\StockItem;
use App\Modules\Inventory\Infrastructure\Models\StockMovement;
use App\Modules\Inventory\Infrastructure\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUnit('oils', 'Oils Cairo Branch', 'oils-cairo', 'Oils Main Warehouse', 'oils-main');
        $this->seedUnit('dates', 'Ghosoun Cairo Branch', 'dates-cairo', 'Dates Main Warehouse', 'dates-main');
    }

    private function seedUnit(string $businessSlug, string $branchName, string $branchSlug, string $warehouseName, string $warehouseSlug): void
    {
        $unit = BusinessUnit::query()->where('slug', $businessSlug)->firstOrFail();
        $branch = Branch::query()->updateOrCreate(
            ['business_unit_id' => $unit->id, 'slug' => $branchSlug],
            [
                'name_ar' => $branchName,
                'name_en' => $branchName,
                'status' => 'active',
                'governorate' => 'Cairo',
                'city' => 'Cairo',
                'is_public' => true,
                'sort_order' => 1,
            ],
        );
        $warehouse = Warehouse::query()->updateOrCreate(
            ['business_unit_id' => $unit->id, 'slug' => $warehouseSlug],
            [
                'branch_id' => $branch->id,
                'name_ar' => $warehouseName,
                'name_en' => $warehouseName,
                'type' => 'main',
                'status' => 'active',
                'governorate' => 'Cairo',
                'city' => 'Cairo',
                'is_default' => true,
                'is_sellable' => true,
                'sort_order' => 1,
            ],
        );

        Product::query()->where('business_unit_id', $unit->id)->with('variants')->get()->each(function (Product $product) use ($unit, $warehouse): void {
            $stockItem = StockItem::query()->updateOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'product_variant_id' => null],
                [
                    'business_unit_id' => $unit->id,
                    'sku' => $product->sku,
                    'quantity_on_hand' => 75,
                    'quantity_reserved' => 0,
                    'reorder_level' => 10,
                    'last_movement_at' => now(),
                ],
            );
            StockMovement::query()->updateOrCreate(
                ['stock_item_id' => $stockItem->id, 'type' => StockMovementType::Receive->value, 'reason' => StockMovementReason::OpeningBalance->value],
                [
                    'business_unit_id' => $unit->id,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 75,
                    'quantity_before' => 0,
                    'quantity_after' => 75,
                    'note' => 'Opening balance.',
                ],
            );
        });
    }
}
