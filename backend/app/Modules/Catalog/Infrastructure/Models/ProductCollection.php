<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCollection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'name_ar', 'name_en', 'slug', 'description_ar', 'description_en',
        'image', 'status', 'starts_at', 'ends_at', 'is_featured', 'sort_order',
        'seo_title_ar', 'seo_title_en', 'seo_description_ar', 'seo_description_en',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_featured' => 'boolean', 'sort_order' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductCollectionItem::class)->orderByDesc('is_featured')->orderBy('sort_order');
    }
}
