<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealEstateProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'name', 'name_ar', 'name_en', 'slug', 'project_code', 'status', 'project_type',
        'developer_name', 'description_ar', 'description_en', 'address', 'city', 'governorate',
        'latitude', 'longitude', 'featured_image', 'gallery_json', 'amenities_json', 'delivery_date',
        'starting_price', 'currency', 'is_featured', 'seo_title_ar', 'seo_title_en',
        'seo_description_ar', 'seo_description_en',
    ];

    protected function casts(): array
    {
        return [
            'gallery_json' => 'array',
            'amenities_json' => 'array',
            'delivery_date' => 'date',
            'starting_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    protected static function booted(): void
    {
        static::saving(function (RealEstateProject $project): void {
            $project->name ??= $project->name_ar;
            $project->name_ar ??= $project->name;
            $project->project_code ??= strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $project->slug), 0, 12));
            $project->project_type ??= 'residential';
        });
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class, 'project_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'real_estate_project_id');
    }
}
