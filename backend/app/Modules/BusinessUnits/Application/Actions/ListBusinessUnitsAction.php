<?php

namespace App\Modules\BusinessUnits\Application\Actions;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBusinessUnitsAction extends BaseAction
{
    public function handle(mixed ...$arguments): LengthAwarePaginator
    {
        $perPage = $arguments[0] ?? 15;

        return BusinessUnit::query()
            ->withCount(['moduleAssignments as enabled_modules_count' => fn ($query) => $query->where('is_enabled', true)])
            ->latest()
            ->paginate($perPage);
    }
}
