<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductPrice;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
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
        $unitPrice = $this->resolvePrice($product, $variant?->id);
        if ($unitPrice === null) {
            throw ValidationException::withMessages(['product_id' => ['Product does not have an active price.']]);
        }
        $item = CartItem::query()->where('cart_id', $cart->id)->where('product_id', $product->id)->where('product_variant_id', $variant?->id)->first();
        $newQuantity = ($item?->quantity ?? 0) + $quantity;
        if ($product->max_order_quantity && $newQuantity > $product->max_order_quantity) {
            throw ValidationException::withMessages(['quantity' => ['Quantity exceeds product order limit.']]);
        }
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
                'metadata_json' => ['price_snapshot_at' => now()->toISOString()],
            ],
        );

        return app(RecalculateCartTotalsAction::class)->handle($cart);
    }

    private function resolvePrice(Product $product, ?int $variantId): ?float
    {
        $defaultList = PriceList::query()->where('business_unit_id', $product->business_unit_id)->where('is_active', true)->where('is_default', true)->where('type', 'retail')->first()
            ?? PriceList::query()->where('business_unit_id', $product->business_unit_id)->where('is_active', true)->where('type', 'retail')->first();
        $query = ProductPrice::query()->where('business_unit_id', $product->business_unit_id)->where('product_id', $product->id)->where('is_active', true);
        if ($defaultList) {
            $query->where('price_list_id', $defaultList->id);
        }
        $price = $variantId ? (clone $query)->where('product_variant_id', $variantId)->orderBy('min_quantity')->first() : null;
        $price ??= (clone $query)->whereNull('product_variant_id')->orderBy('min_quantity')->first();

        return $price?->price ? (float) $price->price : ($product->base_price !== null ? (float) $product->base_price : null);
    }
}
