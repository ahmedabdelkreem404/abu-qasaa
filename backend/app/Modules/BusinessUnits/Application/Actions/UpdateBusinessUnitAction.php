<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Support\Arr;

class UpdateBusinessUnitAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $arguments[0];
        $attributes = Arr::except($arguments[1] ?? [], ['template_key']);

        $businessUnit->update($attributes);

        return $businessUnit->refresh()->load(['moduleAssignments.activityModule', 'settings']);
    }
}
