<?php

namespace App\Modules\Commerce\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Inventory\Infrastructure\Models\StockReservation;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_unit_id', 'customer_id', 'order_number', 'status', 'payment_status', 'fulfillment_status', 'currency', 'subtotal', 'discount_total', 'tax_total', 'shipping_total', 'grand_total', 'customer_name', 'customer_email', 'customer_phone', 'shipping_address_json', 'billing_address_json', 'notes', 'internal_notes', 'source', 'placed_at', 'confirmed_at', 'cancelled_at', 'metadata_json', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'shipping_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'shipping_address_json' => 'array', 'billing_address_json' => 'array', 'metadata_json' => 'array', 'placed_at' => 'datetime', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function manualPaymentProofs(): HasMany
    {
        return $this->hasMany(ManualPaymentProof::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
