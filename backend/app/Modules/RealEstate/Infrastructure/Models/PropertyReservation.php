<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyReservation extends Model
{
    protected $fillable = [
        'business_unit_id', 'lead_id', 'customer_id', 'project_id', 'unit_id', 'reservation_number',
        'status', 'reservation_amount', 'currency', 'reserved_at', 'expires_at', 'cancelled_at',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['reservation_amount' => 'decimal:2', 'reserved_at' => 'datetime', 'expires_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(RealEstateLead::class, 'lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
