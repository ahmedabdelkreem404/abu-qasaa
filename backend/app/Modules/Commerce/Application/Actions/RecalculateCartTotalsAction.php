<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;

class RecalculateCartTotalsAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        $cart = $arguments[0];
        $subtotal = $cart->items()->sum('subtotal');
        $cart->update([
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'grand_total' => $subtotal,
        ]);

        return $cart->refresh()->load(['businessUnit', 'items']);
    }
}
