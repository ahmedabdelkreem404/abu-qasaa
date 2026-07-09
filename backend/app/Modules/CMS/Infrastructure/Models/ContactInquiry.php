<?php

namespace App\Modules\CMS\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiry extends Model
{
    protected $fillable = [
        'business_unit_id', 'name', 'email', 'phone', 'subject', 'message',
        'source_page', 'status', 'assigned_to', 'metadata_json',
    ];

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
