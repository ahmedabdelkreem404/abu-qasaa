<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;

class DeleteOrArchiveBusinessUnitAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $arguments[0];
        $businessUnit->update(['status' => 'archived']);

        return $businessUnit->refresh();
    }
}
