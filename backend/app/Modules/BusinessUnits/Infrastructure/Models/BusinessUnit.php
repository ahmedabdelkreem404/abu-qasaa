<?php

namespace App\Modules\BusinessUnits\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessUnit extends Model
{
    protected $fillable = [
        'parent_id',
        'name_ar',
        'name_en',
        'slug',
        'type',
        'status',
        'logo',
        'cover_image',
        'description',
        'primary_color',
        'secondary_color',
        'settings_json',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'settings_json' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function moduleAssignments(): HasMany
    {
        return $this->hasMany(BusinessUnitModule::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(ActivityModule::class, 'business_unit_modules')
            ->withPivot(['is_enabled', 'settings_json'])
            ->withTimestamps();
    }

    public function settings(): HasMany
    {
        return $this->hasMany(BusinessUnitSetting::class);
    }

    public function featureFlags(): HasMany
    {
        return $this->hasMany(FeatureFlag::class);
    }
}
