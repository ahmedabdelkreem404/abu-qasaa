<?php

namespace App\Modules\Commerce\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'user_id', 'type', 'name', 'email', 'phone', 'company_name',
        'tax_number', 'commercial_record', 'approval_status', 'wholesale_status',
        'price_list_id', 'approved_at', 'approved_by', 'rejected_at', 'rejected_by',
        'rejection_reason', 'credit_limit', 'payment_terms', 'assigned_sales_user_id',
        'wholesale_access_token_hash', 'notes', 'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
