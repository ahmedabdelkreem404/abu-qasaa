<?php

namespace App\Modules\CMS\Application\Actions;

use App\Models\User;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCmsPagesAction extends BaseAction
{
    public function handle(mixed ...$arguments): LengthAwarePaginator
    {
        /** @var User $user */
        $user = $arguments[0];
        $filters = $arguments[1] ?? [];

        $query = CmsPage::query()->with('businessUnit')->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['business_unit_id'])) {
            $query->where('business_unit_id', $filters['business_unit_id']);
        }

        if (! $user->isSuperAdmin()) {
            $assignedIds = $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id');
            $query->whereIn('business_unit_id', $assignedIds);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
