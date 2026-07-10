<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;

class ClearCartAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        $cart = $arguments[0];
        $cart->items()->delete();

        return app(RecalculateCartTotalsAction::class)->handle($cart);
    }
}
