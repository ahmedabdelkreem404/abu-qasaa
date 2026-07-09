<?php

namespace App\Modules\Core\Infrastructure\Scopes;

use Illuminate\Database\Eloquent\Builder;

final class BusinessUnitScope
{
    public static function apply(Builder $query, int|string|null $businessUnitId): Builder
    {
        if ($businessUnitId === null) {
            return $query;
        }

        return $query->where('business_unit_id', $businessUnitId);
    }
}
