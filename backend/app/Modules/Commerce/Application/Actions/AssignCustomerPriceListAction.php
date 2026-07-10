<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

class AssignCustomerPriceListAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        [$customer, $data] = $arguments;
        $priceList = PriceList::query()
            ->whereKey($data['price_list_id'])
            ->where('business_unit_id', $customer->business_unit_id)
            ->where('is_active', true)
            ->first();

        if (! $priceList || ! in_array($priceList->type, ['wholesale', 'distributor', 'special'], true)) {
            throw ValidationException::withMessages(['price_list_id' => ['Price list must be active, wholesale-capable, and belong to the same business unit.']]);
        }

        $customer->update(['price_list_id' => $priceList->id]);

        return $customer->load(['businessUnit', 'priceList']);
    }
}
