<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class GetCustomerAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        return $arguments[0]->load(['businessUnit', 'addresses', 'orders']);
    }
}
