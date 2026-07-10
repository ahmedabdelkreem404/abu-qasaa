<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\CustomerAddress;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateCustomerAddressAction extends BaseAction
{
    public function handle(mixed ...$arguments): CustomerAddress
    {
        [$customer, $data] = $arguments;

        return CustomerAddress::query()->create([...$data, 'customer_id' => $customer->id]);
    }
}
