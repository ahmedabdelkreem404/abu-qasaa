<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Application\DTOs\BusinessUnitDTO;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateBusinessUnitAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnitDTO $dto */
        $dto = $arguments[0];

        return BusinessUnit::query()->create($dto->toArray());
    }
}
