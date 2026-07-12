<?php

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorporateGiftInquiry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'product_id', 'product_collection_id', 'company_name', 'contact_name',
        'phone', 'email', 'quantity', 'budget_range', 'occasion', 'message', 'status',
        'assigned_to', 'metadata_json',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'metadata_json' => 'array'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(ProductCollection::class, 'product_collection_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
