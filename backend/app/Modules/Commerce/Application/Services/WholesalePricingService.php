<?php

namespace App\Modules\Commerce\Application\Services;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductPrice;
use App\Modules\Commerce\Domain\Enums\PriceAudience;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use Illuminate\Validation\ValidationException;

class WholesalePricingService
{
    public function resolvePriceList(Customer $customer): ?PriceList
    {
        if ($customer->price_list_id) {
            $assigned = PriceList::query()
                ->whereKey($customer->price_list_id)
                ->where('business_unit_id', $customer->business_unit_id)
                ->where('is_active', true)
                ->whereIn('type', [PriceAudience::Wholesale->value, PriceAudience::Distributor->value, PriceAudience::Special->value])
                ->first();

            if ($assigned) {
                return $assigned;
            }
        }

        return PriceList::query()
            ->where('business_unit_id', $customer->business_unit_id)
            ->where('is_active', true)
            ->where('type', PriceAudience::Wholesale->value)
            ->orderByDesc('is_default')
            ->first();
    }

    public function resolveProductPrice(Product $product, Customer $customer, int $quantity, ?int $variantId = null): array
    {
        $priceList = $this->resolvePriceList($customer);
        if (! $priceList) {
            throw ValidationException::withMessages(['price_list_id' => ['No active wholesale price list is available for this customer.']]);
        }

        $query = ProductPrice::query()
            ->where('business_unit_id', $product->business_unit_id)
            ->where('product_id', $product->id)
            ->where('price_list_id', $priceList->id)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));

        $price = $variantId ? (clone $query)->where('product_variant_id', $variantId)->orderByDesc('min_quantity')->first() : null;
        $price ??= (clone $query)->whereNull('product_variant_id')->orderByDesc('min_quantity')->first();

        if (! $price) {
            throw ValidationException::withMessages(['product_id' => ['Wholesale price is not configured for this product and quantity.']]);
        }

        return [
            'unit_price' => (float) $price->price,
            'price_list_id' => $priceList->id,
            'price_list_type' => $priceList->type,
            'price_audience' => $priceList->type,
            'min_quantity_applied' => $price->min_quantity,
            'price_source' => 'wholesale_price_list',
        ];
    }
}
