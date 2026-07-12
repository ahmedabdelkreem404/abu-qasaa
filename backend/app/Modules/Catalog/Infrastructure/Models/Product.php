<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'category_id', 'brand_id', 'name_ar', 'name_en', 'slug', 'sku',
        'product_type', 'status', 'visibility', 'short_description_ar', 'short_description_en',
        'description_ar', 'description_en', 'featured_image', 'base_price', 'compare_at_price',
        'cost_price', 'currency', 'is_featured', 'is_taxable', 'min_order_quantity',
        'max_order_quantity', 'specs_json', 'merchandising_json', 'gift_options_json', 'seo_title_ar', 'seo_title_en',
        'seo_description_ar', 'seo_description_en', 'published_at', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_taxable' => 'boolean',
            'min_order_quantity' => 'integer',
            'max_order_quantity' => 'integer',
            'specs_json' => 'array',
            'merchandising_json' => 'array',
            'gift_options_json' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(ProductBadge::class, 'product_badge_product')->withTimestamps();
    }

    public function bundle(): HasOne
    {
        return $this->hasOne(ProductBundle::class);
    }
}
