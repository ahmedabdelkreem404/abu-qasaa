<?php

namespace App\Modules\Inventory\Infrastructure\Models;

use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Commerce\Infrastructure\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = ['business_unit_id', 'order_id', 'order_item_id', 'warehouse_id', 'product_id', 'product_variant_id', 'quantity', 'status', 'reserved_at', 'released_at', 'fulfilled_at', 'metadata_json'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'reserved_at' => 'datetime', 'released_at' => 'datetime', 'fulfilled_at' => 'datetime', 'metadata_json' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
