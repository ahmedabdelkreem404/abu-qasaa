<?php

namespace App\Modules\Commerce\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WholesaleApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id',
        'customer_id',
        'status',
        'applicant_name',
        'phone',
        'email',
        'company_name',
        'shop_name',
        'tax_number',
        'commercial_record',
        'governorate',
        'city',
        'address',
        'requested_price_list_id',
        'message',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function requestedPriceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'requested_price_list_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
