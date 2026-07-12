<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleItem extends Model
{
    protected $fillable = ['product_bundle_id', 'child_product_id', 'child_product_variant_id', 'quantity', 'sort_order', 'metadata_json'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'sort_order' => 'integer', 'metadata_json' => 'array'];
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    public function childProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'child_product_id');
    }

    public function childVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'child_product_variant_id');
    }
}
