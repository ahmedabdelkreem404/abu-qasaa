<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateCustomerAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        return Customer::query()->create($arguments[0]);
    }
}
