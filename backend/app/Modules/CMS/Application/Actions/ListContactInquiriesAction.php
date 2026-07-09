<?php

namespace App\Modules\CMS\Application\Actions;

use App\Models\User;
use App\Modules\CMS\Infrastructure\Models\ContactInquiry;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListContactInquiriesAction extends BaseAction
{
    public function handle(mixed ...$arguments): LengthAwarePaginator
    {
        /** @var User $user */
        $user = $arguments[0];

        $query = ContactInquiry::query()->with('businessUnit')->latest();

        if (! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }

        return $query->paginate(15);
    }
}
