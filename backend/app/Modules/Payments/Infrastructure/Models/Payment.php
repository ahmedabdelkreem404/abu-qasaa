<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['business_unit_id', 'order_id', 'customer_id', 'payment_method_id', 'method_type', 'method_key', 'status', 'amount', 'currency', 'paid_at', 'failed_at', 'reference', 'notes', 'metadata_json', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'metadata_json' => 'array', 'paid_at' => 'datetime', 'failed_at' => 'datetime'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class)->latest();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
