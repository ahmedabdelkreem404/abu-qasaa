<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class RfqQuotationItem extends Model
{
    protected $fillable = ['quotation_id', 'rfq_item_id', 'description', 'quantity', 'unit', 'unit_price', 'subtotal'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }
}
