<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Domain\Enums\WholesaleApplicationStatus;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Core\Application\Actions\BaseAction;

class SubmitWholesaleApplicationAction extends BaseAction
{
    public function handle(mixed ...$arguments): WholesaleApplication
    {
        [$businessUnit, $data] = $arguments;

        return WholesaleApplication::query()->create([
            ...$data,
            'business_unit_id' => $businessUnit instanceof BusinessUnit ? $businessUnit->id : $businessUnit,
            'status' => WholesaleApplicationStatus::Pending->value,
        ])->load(['businessUnit', 'requestedPriceList']);
    }
}
