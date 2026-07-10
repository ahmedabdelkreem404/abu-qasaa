<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateWholesaleCustomerAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        [$customer, $data] = $arguments;
        $customer->update($data);

        return $customer->load(['businessUnit', 'priceList']);
    }
}
