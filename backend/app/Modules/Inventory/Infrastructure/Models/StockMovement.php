<?php

namespace App\Modules\Inventory\Infrastructure\Models;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['business_unit_id', 'warehouse_id', 'product_id', 'product_variant_id', 'stock_item_id', 'type', 'reason', 'quantity', 'quantity_before', 'quantity_after', 'reference_type', 'reference_id', 'note', 'created_by', 'metadata_json'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'quantity_before' => 'decimal:3', 'quantity_after' => 'decimal:3', 'metadata_json' => 'array'];
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
