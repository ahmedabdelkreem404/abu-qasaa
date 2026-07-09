<?php

namespace App\Modules\Identity\Application\Services;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;

class AccessControlService
{
    public function canAccessBusinessUnit(User $user, BusinessUnit|int|string|null $businessUnit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $businessUnitId = $businessUnit instanceof BusinessUnit ? $businessUnit->id : $businessUnit;

        if ($businessUnitId === null) {
            return false;
        }

        return $user->businessUnitAssignments()
            ->where('business_unit_id', $businessUnitId)
            ->where('is_active', true)
            ->exists();
    }

    public function canUseModule(User $user, BusinessUnit $businessUnit, string $moduleKey): bool
    {
        if (! $this->canAccessBusinessUnit($user, $businessUnit)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $businessUnit->moduleAssignments()
            ->whereHas('activityModule', fn ($query) => $query->where('key', $moduleKey))
            ->where('is_enabled', true)
            ->exists();
    }
}
