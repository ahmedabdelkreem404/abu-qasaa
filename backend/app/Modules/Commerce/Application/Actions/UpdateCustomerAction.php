<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateCustomerAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        $customer = $arguments[0];
        $customer->update($arguments[1]);

        return $customer->refresh();
    }
}
