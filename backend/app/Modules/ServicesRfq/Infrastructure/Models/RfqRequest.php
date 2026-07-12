<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqRequest extends Model
{
    protected $fillable = ['business_unit_id', 'service_id', 'number', 'rfq_number', 'customer_id', 'company_name', 'contact_name', 'phone', 'email', 'origin_country', 'destination_country', 'shipping_method', 'incoterm', 'currency', 'expected_date', 'notes', 'status', 'assigned_to', 'submitted_at'];

    protected function casts(): array
    {
        return ['expected_date' => 'date', 'submitted_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (RfqRequest $rfq): void {
            $rfq->rfq_number ??= $rfq->number;
            $rfq->number ??= $rfq->rfq_number;
            $rfq->submitted_at ??= now();
        });
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }
}
