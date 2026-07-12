<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundle extends Model
{
    protected $fillable = [
        'business_unit_id', 'product_id', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'bundle_type', 'pricing_mode', 'fixed_price', 'is_active', 'metadata_json',
    ];

    protected function casts(): array
    {
        return ['fixed_price' => 'decimal:2', 'is_active' => 'boolean', 'metadata_json' => 'array'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class)->orderBy('sort_order');
    }
}
