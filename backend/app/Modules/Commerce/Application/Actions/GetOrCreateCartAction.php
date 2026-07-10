<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Cart;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Support\Str;

class GetOrCreateCartAction extends BaseAction
{
    public function handle(mixed ...$arguments): Cart
    {
        [$businessUnit, $sessionToken] = $arguments + [null, null];
        $sessionToken = $sessionToken ?: Str::uuid()->toString();

        return Cart::query()->firstOrCreate(
            ['business_unit_id' => $businessUnit->id, 'session_token' => $sessionToken, 'status' => 'active'],
            ['currency' => 'EGP', 'expires_at' => now()->addDays(14)],
        )->load(['businessUnit', 'items']);
    }
}
