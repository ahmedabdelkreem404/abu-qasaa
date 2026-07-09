<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBusinessUnitsAction extends BaseAction
{
    public function handle(mixed ...$arguments): LengthAwarePaginator
    {
        $perPage = $arguments[0] ?? 15;
        /** @var User|null $user */
        $user = $arguments[1] ?? null;

        $query = BusinessUnit::query()
            ->withCount(['moduleAssignments as enabled_modules_count' => fn ($query) => $query->where('is_enabled', true)])
            ->latest();

        if ($user && ! $user->isSuperAdmin()) {
            $query->whereIn('id', $user->businessUnitAssignments()
                ->where('is_active', true)
                ->select('business_unit_id'));
        }

        return $query->paginate($perPage);
    }
}
