<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_unit_id', 'real_estate_project_id', 'title', 'type', 'name', 'code', 'property_type', 'status', 'floors_count', 'metadata_json', 'description'];

    protected function casts(): array
    {
        return ['metadata_json' => 'array', 'floors_count' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            $property->title ??= $property->name;
            $property->name ??= $property->title;
            $property->type ??= $property->property_type;
            $property->property_type ??= $property->type ?? 'building';
            $property->code ??= strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $property->name ?? 'property'), 0, 12));
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'real_estate_project_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class);
    }
}
