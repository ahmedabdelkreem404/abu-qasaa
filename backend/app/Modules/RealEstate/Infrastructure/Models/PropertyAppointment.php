<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAppointment extends Model
{
    protected $fillable = ['business_unit_id', 'lead_id', 'project_id', 'unit_id', 'assigned_user_id', 'scheduled_at', 'duration_minutes', 'location', 'status', 'notes', 'outcome'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'duration_minutes' => 'integer'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(RealEstateLead::class, 'lead_id');
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
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
