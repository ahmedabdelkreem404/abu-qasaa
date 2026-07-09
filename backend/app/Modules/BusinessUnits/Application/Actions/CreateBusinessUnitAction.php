<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Application\DTOs\BusinessUnitDTO;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityTemplate;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateBusinessUnitAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnitDTO $dto */
        $dto = $arguments[0];
        $attributes = Arr::except($dto->toArray(), ['template_key']);

        return DB::transaction(function () use ($attributes, $dto): BusinessUnit {
            $businessUnit = BusinessUnit::query()->create($attributes);

            if ($dto->template_key !== null) {
                $template = ActivityTemplate::query()->where('key', $dto->template_key)->first();

                if ($template !== null) {
                    app(SyncBusinessUnitModulesAction::class)->handle($businessUnit, $template->default_modules_json ?? []);
                    app(UpdateBusinessUnitSettingsAction::class)->handle($businessUnit, $template->default_settings_json ?? []);
                }
            }

            return $businessUnit->load(['moduleAssignments.activityModule', 'settings']);
        });
    }
}
