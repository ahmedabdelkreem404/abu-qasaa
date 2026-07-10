<?php

namespace App\Modules\Inventory\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_unit_id', 'branch_id', 'name_ar', 'name_en', 'slug', 'type', 'status', 'phone', 'address_ar', 'address_en', 'governorate', 'city', 'area', 'is_default', 'is_sellable', 'sort_order'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_sellable' => 'boolean', 'sort_order' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
