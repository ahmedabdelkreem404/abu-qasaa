<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Application\Services\WholesalePricingService;
use App\Modules\Core\Application\Actions\BaseAction;

class GetWholesaleCatalogPricingAction extends BaseAction
{
    public function handle(mixed ...$arguments): array
    {
        [$products, $customer] = $arguments;
        $service = app(WholesalePricingService::class);

        return collect($products)->mapWithKeys(function (Product $product) use ($customer, $service): array {
            try {
                return [$product->id => $service->resolveProductPrice($product, $customer, max(1, $product->min_order_quantity))];
            } catch (\Throwable) {
                return [$product->id => null];
            }
        })->all();
    }
}
