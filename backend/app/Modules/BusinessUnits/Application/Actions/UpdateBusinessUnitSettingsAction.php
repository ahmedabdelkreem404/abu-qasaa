<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateBusinessUnitSettingsAction extends BaseAction
{
    public function handle(mixed ...$arguments): BusinessUnit
    {
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $arguments[0];
        $settings = $arguments[1] ?? [];

        foreach ($settings as $key => $value) {
            $payload = is_array($value) && array_key_exists('value', $value) ? $value : ['value' => $value];

            BusinessUnitSetting::query()->updateOrCreate(
                [
                    'business_unit_id' => $businessUnit->id,
                    'key' => $key,
                ],
                [
                    'value' => $payload['value'],
                    'type' => $payload['type'] ?? null,
                    'group' => $payload['group'] ?? null,
                ],
            );
        }

        return $businessUnit->refresh()->load('settings');
    }
}
