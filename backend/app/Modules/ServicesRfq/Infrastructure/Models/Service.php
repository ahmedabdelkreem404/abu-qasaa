<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = ['business_unit_id', 'category', 'name', 'name_ar', 'name_en', 'slug', 'summary_ar', 'summary_en', 'description', 'description_ar', 'description_en', 'featured_image', 'status', 'is_featured', 'sort_order'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            $service->name ??= $service->name_en ?? $service->name_ar;
            $service->name_ar ??= $service->name;
        });
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
