<?php

namespace App\Modules\BusinessUnits\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessUnitModule extends Model
{
    protected $fillable = [
        'business_unit_id',
        'activity_module_id',
        'is_enabled',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings_json' => 'array',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function activityModule(): BelongsTo
    {
        return $this->belongsTo(ActivityModule::class);
    }
}
