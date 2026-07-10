<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;

class GetCartAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        [$businessUnit, $sessionToken] = $arguments;

        return Cart::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('session_token', $sessionToken)
            ->with(['businessUnit', 'items'])
            ->firstOrFail();
    }
}
