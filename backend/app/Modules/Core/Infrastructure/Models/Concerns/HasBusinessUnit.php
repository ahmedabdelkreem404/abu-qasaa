<?php

namespace App\Modules\Core\Infrastructure\Models\Concerns;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasBusinessUnit
{
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
