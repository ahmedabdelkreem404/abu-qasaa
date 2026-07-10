<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['payment_id', 'type', 'status', 'amount', 'currency', 'reference', 'provider', 'provider_transaction_id', 'provider_order_id', 'provider_status', 'payload_json', 'raw_payload_json', 'processed_at', 'verified_at', 'created_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'payload_json' => 'array', 'raw_payload_json' => 'array', 'processed_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
