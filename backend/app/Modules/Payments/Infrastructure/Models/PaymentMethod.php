<?php

namespace App\Modules\Payments\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_unit_id', 'key', 'type', 'name_ar', 'name_en', 'description_ar', 'description_en', 'instructions_ar', 'instructions_en', 'destination_account', 'destination_account_name', 'config_json', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['config_json' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
