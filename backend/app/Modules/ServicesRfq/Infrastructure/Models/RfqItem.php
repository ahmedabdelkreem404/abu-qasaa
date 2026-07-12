<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    protected $fillable = ['business_unit_id', 'rfq_request_id', 'service_id', 'item_name', 'description', 'quantity', 'unit', 'target_price', 'specifications_json'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'target_price' => 'decimal:2', 'specifications_json' => 'array'];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RfqRequest::class, 'rfq_request_id');
    }

    protected static function booted(): void
    {
        static::saving(function (RfqItem $item): void {
            if (! $item->business_unit_id && $item->rfq_request_id) {
                $item->business_unit_id = RfqRequest::query()->whereKey($item->rfq_request_id)->value('business_unit_id');
            }
        });
    }
}
