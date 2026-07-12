<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealEstateLead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'project_id', 'unit_id', 'source', 'name', 'phone', 'email',
        'preferred_contact_method', 'budget_min', 'budget_max', 'message', 'status',
        'assigned_to', 'next_follow_up_at', 'metadata_json',
    ];

    protected function casts(): array
    {
        return ['budget_min' => 'decimal:2', 'budget_max' => 'decimal:2', 'next_follow_up_at' => 'datetime', 'metadata_json' => 'array'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
