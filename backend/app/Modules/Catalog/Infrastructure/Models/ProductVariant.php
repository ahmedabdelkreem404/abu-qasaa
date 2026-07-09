<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name_ar', 'name_en', 'sku', 'barcode', 'option_values_json',
        'price_adjustment', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'option_values_json' => 'array',
            'price_adjustment' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
