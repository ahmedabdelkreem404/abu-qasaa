<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentPlan extends Model
{
    protected $fillable = ['business_unit_id', 'project_id', 'unit_id', 'name', 'down_payment', 'installment_count', 'frequency', 'installment_amount', 'currency', 'is_active'];

    protected function casts(): array
    {
        return ['down_payment' => 'decimal:2', 'installment_amount' => 'decimal:2', 'installment_count' => 'integer', 'is_active' => 'boolean'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InstallmentPlanItem::class);
    }
}
