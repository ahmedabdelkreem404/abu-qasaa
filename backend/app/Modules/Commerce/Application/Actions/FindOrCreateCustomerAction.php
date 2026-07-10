<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Core\Application\Actions\BaseAction;

class FindOrCreateCustomerAction extends BaseAction
{
    public function handle(mixed ...$arguments): Customer
    {
        [$businessUnitId, $data] = $arguments;

        return Customer::query()->firstOrCreate(
            ['business_unit_id' => $businessUnitId, 'phone' => $data['phone']],
            ['type' => $data['type'] ?? 'individual', 'name' => $data['name'], 'email' => $data['email'] ?? null],
        );
    }
}
