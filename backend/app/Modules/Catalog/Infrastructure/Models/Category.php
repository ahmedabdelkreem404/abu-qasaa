<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'parent_id', 'name_ar', 'name_en', 'slug',
        'description_ar', 'description_en', 'image', 'status', 'sort_order',
        'seo_title_ar', 'seo_title_en', 'seo_description_ar', 'seo_description_en',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
