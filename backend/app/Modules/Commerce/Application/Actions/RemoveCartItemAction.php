<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;

class RemoveCartItemAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        [$cart, $item] = $arguments;
        $item->delete();

        return app(RecalculateCartTotalsAction::class)->handle($cart);
    }
}
