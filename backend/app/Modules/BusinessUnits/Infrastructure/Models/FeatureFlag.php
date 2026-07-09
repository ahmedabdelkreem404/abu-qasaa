<?php

namespace App\Modules\BusinessUnits\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlag extends Model
{
    protected $fillable = [
        'business_unit_id',
        'key',
        'value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'boolean',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
