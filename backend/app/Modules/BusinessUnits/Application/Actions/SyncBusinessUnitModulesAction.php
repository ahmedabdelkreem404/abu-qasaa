<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\ActivityModule;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitModule;
use App\Modules\Core\Application\Actions\BaseAction;

class SyncBusinessUnitModulesAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $arguments[0];
        $modules = collect($arguments[1] ?? []);

        $requestedKeys = $modules->map(fn ($module) => is_array($module) ? ($module['key'] ?? null) : $module)
            ->filter()
            ->values();

        $activityModules = ActivityModule::query()
            ->whereIn('key', $requestedKeys)
            ->get();

        $activityModules->each(function (ActivityModule $activityModule) use ($businessUnit, $modules): void {
            $payload = $modules->first(fn ($module) => (is_array($module) ? ($module['key'] ?? null) : $module) === $activityModule->key);

            BusinessUnitModule::query()->updateOrCreate(
                [
                    'business_unit_id' => $businessUnit->id,
                    'activity_module_id' => $activityModule->id,
                ],
                [
                    'is_enabled' => is_array($payload) ? (bool) ($payload['is_enabled'] ?? true) : true,
                    'settings_json' => is_array($payload) ? ($payload['settings_json'] ?? null) : null,
                ],
            );
        });

        BusinessUnitModule::query()
            ->where('business_unit_id', $businessUnit->id)
            ->whereNotIn('activity_module_id', $activityModules->pluck('id'))
            ->update(['is_enabled' => false]);

        return $businessUnit->refresh()->load('moduleAssignments.activityModule');
    }
}
