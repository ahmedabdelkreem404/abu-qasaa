<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        [$cart, $item, $quantity] = $arguments;
        $product = $item->product;
        if ($quantity < $product->min_order_quantity || ($product->max_order_quantity && $quantity > $product->max_order_quantity)) {
            throw ValidationException::withMessages(['quantity' => ['Quantity is outside product order limits.']]);
        }
        $item->update(['quantity' => $quantity, 'subtotal' => (float) $item->unit_price * $quantity]);

        return app(RecalculateCartTotalsAction::class)->handle($cart);
    }
}
