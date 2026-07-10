<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Brand;
use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductPrice;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOils();
        $this->seedDates();
    }

    private function seedOils(): void
    {
        $unit = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        $categories = $this->categories($unit, [['Engine Oils', 'engine-oils'], ['Gear Oils', 'gear-oils'], ['Greases', 'greases']]);
        $brands = $this->brands($unit, [['Abu Qasaa Select', 'abu-qasaa-select'], ['Partner Lubricants', 'partner-lubricants']]);
        $priceLists = $this->priceLists($unit, [['Retail', 'retail', 'retail', true], ['Wholesale', 'wholesale', 'wholesale', false], ['Distributor', 'distributor', 'distributor', false]]);

        $this->product($unit, $categories['engine-oils'], $brands['abu-qasaa-select'], 'Premium Engine Oil 4L', 'premium-engine-oil-4l', 'OIL-ENG-4L', 420, ['viscosity_grade' => '10W-40', 'oil_type' => 'synthetic_blend', 'package_size' => '4L', 'vehicle_type' => 'passenger_car', 'api_standard' => 'SN', 'origin_country' => 'Egypt'], ['1L', '4L', '20L'], $priceLists, [420, 360]);
        $this->product($unit, $categories['engine-oils'], $brands['partner-lubricants'], 'Heavy Duty Diesel Oil 20L', 'heavy-duty-diesel-oil-20l', 'OIL-DIESEL-20L', 1850, ['viscosity_grade' => '15W-40', 'oil_type' => 'diesel', 'package_size' => '20L', 'vehicle_type' => 'heavy_duty', 'api_standard' => 'CI-4', 'origin_country' => 'Egypt'], ['20L'], $priceLists, [1850, 1625]);
        $this->product($unit, $categories['greases'], $brands['abu-qasaa-select'], 'Multi-Purpose Grease', 'multi-purpose-grease', 'GREASE-MP', 210, ['oil_type' => 'grease', 'package_size' => '1kg', 'vehicle_type' => 'general', 'origin_country' => 'Egypt'], ['500g', '1kg', '5kg'], $priceLists, [210, 180]);
    }

    private function seedDates(): void
    {
        $unit = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        $categories = $this->categories($unit, [['Premium Dates', 'premium-dates'], ['Gift Boxes', 'gift-boxes'], ['Bulk Dates', 'bulk-dates']]);
        $brands = $this->brands($unit, [['Ghosoun', 'ghosoun']]);
        $priceLists = $this->priceLists($unit, [['Retail', 'retail', 'retail', true]]);

        $this->product($unit, $categories['premium-dates'], $brands['ghosoun'], 'Premium Medjool Dates 1kg', 'premium-medjool-dates-1kg', 'DATES-MEDJOOL-1KG', 280, ['date_type' => 'medjool', 'weight' => '1kg', 'package_type' => 'box', 'grade' => 'premium', 'harvest_season' => '2026', 'is_gift_box' => false], ['500g', '1kg', '5kg'], $priceLists, [280]);
        $this->product($unit, $categories['premium-dates'], $brands['ghosoun'], 'Classic Dates Box 500g', 'classic-dates-box-500g', 'DATES-CLASSIC-500G', 120, ['date_type' => 'classic', 'weight' => '500g', 'package_type' => 'box', 'grade' => 'standard', 'harvest_season' => '2026', 'is_gift_box' => false], ['500g'], $priceLists, [120]);
        $this->product($unit, $categories['gift-boxes'], $brands['ghosoun'], 'Corporate Dates Gift Box', 'corporate-dates-gift-box', 'DATES-GIFT-CORP', 450, ['date_type' => 'assorted', 'weight' => '1kg', 'package_type' => 'gift_box', 'grade' => 'premium', 'harvest_season' => '2026', 'is_gift_box' => true], ['500g', '1kg'], $priceLists, [450]);
    }

    private function categories(BusinessUnit $unit, array $items): array
    {
        $categories = [];
        foreach ($items as $index => [$name, $slug]) {
            $categories[$slug] = Category::query()->updateOrCreate(['business_unit_id' => $unit->id, 'slug' => $slug], ['name_ar' => $name, 'name_en' => $name, 'status' => 'active', 'sort_order' => $index]);
        }

        return $categories;
    }

    private function brands(BusinessUnit $unit, array $items): array
    {
        $brands = [];
        foreach ($items as $index => [$name, $slug]) {
            $brands[$slug] = Brand::query()->updateOrCreate(['business_unit_id' => $unit->id, 'slug' => $slug], ['name_ar' => $name, 'name_en' => $name, 'status' => 'active', 'sort_order' => $index]);
        }

        return $brands;
    }

    private function priceLists(BusinessUnit $unit, array $items): array
    {
        $lists = [];
        foreach ($items as [$name, $key, $type, $isDefault]) {
            $lists[$key] = PriceList::query()->updateOrCreate(['business_unit_id' => $unit->id, 'key' => $key], ['name' => $name, 'type' => $type, 'is_default' => $isDefault, 'is_active' => true]);
        }

        return $lists;
    }

    private function product(BusinessUnit $unit, Category $category, Brand $brand, string $name, string $slug, string $sku, int $basePrice, array $specs, array $variants, array $priceLists, array $prices): void
    {
        $product = Product::query()->updateOrCreate(['business_unit_id' => $unit->id, 'slug' => $slug], [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name_ar' => $name,
            'name_en' => $name,
            'sku' => $sku,
            'product_type' => count($variants) > 1 ? 'variable' : 'simple',
            'status' => 'published',
            'visibility' => 'public',
            'short_description_en' => 'Catalog foundation sample product.',
            'description_en' => 'Seeded sample product for public catalog browsing and dashboard management.',
            'base_price' => $basePrice,
            'currency' => 'EGP',
            'is_featured' => true,
            'specs_json' => $specs,
            'published_at' => now(),
        ]);

        $product->variants()->delete();
        foreach ($variants as $index => $variantName) {
            ProductVariant::query()->create(['product_id' => $product->id, 'name_ar' => $variantName, 'name_en' => $variantName, 'sku' => $sku.'-'.$variantName, 'option_values_json' => ['size' => $variantName], 'sort_order' => $index, 'is_active' => true]);
        }

        $product->prices()->delete();
        $retail = $priceLists['retail'] ?? reset($priceLists);
        ProductPrice::query()->create(['business_unit_id' => $unit->id, 'product_id' => $product->id, 'price_list_id' => $retail->id, 'price' => $prices[0], 'min_quantity' => 1, 'is_active' => true]);
        if (isset($priceLists['wholesale'], $prices[1])) {
            ProductPrice::query()->create(['business_unit_id' => $unit->id, 'product_id' => $product->id, 'price_list_id' => $priceLists['wholesale']->id, 'price' => $prices[1], 'min_quantity' => 12, 'is_active' => true]);
        }
        if (isset($priceLists['distributor'], $prices[1])) {
            ProductPrice::query()->create(['business_unit_id' => $unit->id, 'product_id' => $product->id, 'price_list_id' => $priceLists['distributor']->id, 'price' => max(1, $prices[1] - 25), 'min_quantity' => 48, 'is_active' => true]);
        }
    }
}
