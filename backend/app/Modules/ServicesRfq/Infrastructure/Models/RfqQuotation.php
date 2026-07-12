<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqQuotation extends Model
{
    protected $fillable = ['business_unit_id', 'rfq_request_id', 'quotation_number', 'status', 'subtotal', 'tax_total', 'shipping_total', 'grand_total', 'currency', 'valid_until', 'terms', 'notes', 'created_by', 'approved_by'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'tax_total' => 'decimal:2', 'shipping_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'valid_until' => 'date'];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RfqRequest::class, 'rfq_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqQuotationItem::class, 'quotation_id');
    }
}
