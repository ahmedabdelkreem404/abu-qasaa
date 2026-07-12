<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductPrice;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use App\Modules\Commerce\Application\Services\WholesaleAccessService;
use App\Modules\Commerce\Application\Services\WholesalePricingService;
use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Commerce\Infrastructure\Models\CartItem;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

class AddCartItemAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        [$cart, $data] = $arguments;
        $product = Product::query()->whereKey($data['product_id'])->where('business_unit_id', $cart->business_unit_id)->where('status', 'published')->where('visibility', 'public')->firstOrFail();
        $variant = empty($data['product_variant_id']) ? null : ProductVariant::query()->whereKey($data['product_variant_id'])->where('product_id', $product->id)->where('is_active', true)->firstOrFail();
        $quantity = (int) $data['quantity'];
        if ($quantity < $product->min_order_quantity || ($product->max_order_quantity && $quantity > $product->max_order_quantity)) {
            throw ValidationException::withMessages(['quantity' => ['Quantity is outside product order limits.']]);
        }
        $price = $this->resolvePrice($cart, $product, $quantity, $variant?->id, $data);
        if ($price === null) {
            throw ValidationException::withMessages(['product_id' => ['Product does not have an active price.']]);
        }
        $unitPrice = $price['unit_price'];
        $minimum = (int) ($price['min_quantity_applied'] ?? $product->min_order_quantity);
        if (($price['price_audience'] ?? 'retail') !== 'retail' && $quantity < $minimum) {
            throw ValidationException::withMessages(['quantity' => ["Wholesale minimum quantity is {$minimum}."]]);
        }
        $item = CartItem::query()->where('cart_id', $cart->id)->where('product_id', $product->id)->where('product_variant_id', $variant?->id)->first();
        $newQuantity = ($item?->quantity ?? 0) + $quantity;
        if ($product->max_order_quantity && $newQuantity > $product->max_order_quantity) {
            throw ValidationException::withMessages(['quantity' => ['Quantity exceeds product order limit.']]);
        }
        if (($price['price_audience'] ?? 'retail') !== 'retail' && $newQuantity < $minimum) {
            throw ValidationException::withMessages(['quantity' => ["Wholesale minimum quantity is {$minimum}."]]);
        }
        $bundleSnapshot = $this->bundleSnapshot($product);
        CartItem::query()->updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id, 'product_variant_id' => $variant?->id],
            [
                'sku' => $variant?->sku ?? $product->sku,
                'product_name_ar' => $product->name_ar,
                'product_name_en' => $product->name_en,
                'variant_name_ar' => $variant?->name_ar,
                'variant_name_en' => $variant?->name_en,
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $newQuantity,
                'metadata_json' => [
                    'price_snapshot_at' => now()->toISOString(),
                    'price_list_id' => $price['price_list_id'] ?? null,
                    'price_list_type' => $price['price_list_type'] ?? 'retail',
                    'price_audience' => $price['price_audience'] ?? 'retail',
                    'min_quantity_applied' => $price['min_quantity_applied'] ?? 1,
                    'price_source' => $price['price_source'] ?? 'retail_price_list',
                    'wholesale_customer_id' => $price['wholesale_customer_id'] ?? null,
                ] + ($bundleSnapshot ? ['bundle' => $bundleSnapshot] : []),
            ],
        );

        return app(RecalculateCartTotalsAction::class)->handle($cart);
    }

    private function resolvePrice(Cart $cart, Product $product, int $quantity, ?int $variantId, array $data): ?array
    {
        $bundle = $product->bundle()->where('is_active', true)->first();
        if ($bundle && $bundle->pricing_mode === 'fixed_bundle_price' && $bundle->fixed_price !== null) {
            return [
                'unit_price' => (float) $bundle->fixed_price,
                'price_list_id' => null,
                'price_list_type' => 'retail',
                'price_audience' => 'retail',
                'min_quantity_applied' => 1,
                'price_source' => 'fixed_bundle_price',
            ];
        }

        $customer = app(WholesaleAccessService::class)->approvedCustomer($product->businessUnit, $data['wholesale_phone'] ?? null, $data['wholesale_token'] ?? null);
        if ($customer) {
            $cart->update(['customer_id' => $customer->id]);
            $price = app(WholesalePricingService::class)->resolveProductPrice($product, $customer, $quantity, $variantId);

            return [...$price, 'wholesale_customer_id' => $customer->id];
        }

        $defaultList = PriceList::query()->where('business_unit_id', $product->business_unit_id)->where('is_active', true)->where('is_default', true)->where('type', 'retail')->first()
            ?? PriceList::query()->where('business_unit_id', $product->business_unit_id)->where('is_active', true)->where('type', 'retail')->first();
        $query = ProductPrice::query()->where('business_unit_id', $product->business_unit_id)->where('product_id', $product->id)->where('is_active', true);
        if ($defaultList) {
            $query->where('price_list_id', $defaultList->id);
        }
        $price = $variantId ? (clone $query)->where('product_variant_id', $variantId)->orderBy('min_quantity')->first() : null;
        $price ??= (clone $query)->whereNull('product_variant_id')->orderBy('min_quantity')->first();

        $unitPrice = $price?->price ? (float) $price->price : ($product->base_price !== null ? (float) $product->base_price : null);

        return $unitPrice === null ? null : [
            'unit_price' => $unitPrice,
            'price_list_id' => $defaultList?->id,
            'price_list_type' => 'retail',
            'price_audience' => 'retail',
            'min_quantity_applied' => $price?->min_quantity ?? 1,
            'price_source' => $price ? 'retail_price_list' : 'product_base_price',
        ];
    }

    private function bundleSnapshot(Product $product): ?array
    {
        $bundle = $product->bundle()
            ->where('is_active', true)
            ->with('items.childProduct')
            ->first();

        if (! $bundle) {
            return null;
        }

        return [
            'id' => $bundle->id,
            'name_ar' => $bundle->name_ar,
            'name_en' => $bundle->name_en,
            'bundle_type' => $bundle->bundle_type,
            'pricing_mode' => $bundle->pricing_mode,
            'fixed_price' => $bundle->fixed_price,
            'items' => $bundle->items->map(fn ($item) => [
                'child_product_id' => $item->child_product_id,
                'child_product_variant_id' => $item->child_product_variant_id,
                'quantity' => $item->quantity,
                'name_ar' => $item->childProduct?->name_ar,
                'name_en' => $item->childProduct?->name_en,
            ])->values()->all(),
        ];
    }
}
