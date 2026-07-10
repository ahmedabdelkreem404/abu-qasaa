<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

class ValidateWholesaleCartAction extends BaseAction
{
    public function handle(mixed ...$arguments): bool
    {
        [$cart] = $arguments;
        foreach ($cart->items as $item) {
            $metadata = $item->metadata_json ?? [];
            if (($metadata['price_audience'] ?? null) === 'wholesale' && $item->quantity < (int) ($metadata['min_quantity_applied'] ?? 1)) {
                throw ValidationException::withMessages(['quantity' => ['Wholesale minimum quantity is not met.']]);
            }
        }

        return true;
    }
}
