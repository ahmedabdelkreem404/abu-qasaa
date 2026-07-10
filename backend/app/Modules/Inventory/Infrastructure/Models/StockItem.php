<?php

namespace App\Modules\Inventory\Infrastructure\Models;

use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    protected $fillable = ['business_unit_id', 'warehouse_id', 'product_id', 'product_variant_id', 'sku', 'quantity_on_hand', 'quantity_reserved', 'reorder_level', 'max_stock_level', 'last_movement_at'];

    protected function casts(): array
    {
        return ['quantity_on_hand' => 'decimal:3', 'quantity_reserved' => 'decimal:3', 'reorder_level' => 'decimal:3', 'max_stock_level' => 'decimal:3', 'last_movement_at' => 'datetime'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getQuantityAvailableAttribute(): float
    {
        return (float) $this->quantity_on_hand - (float) $this->quantity_reserved;
    }
}
