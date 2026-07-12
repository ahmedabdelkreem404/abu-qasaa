<?php

namespace App\Modules\Audit\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['business_unit_id', 'user_id', 'action', 'event', 'auditable_type', 'auditable_id', 'old_values_json', 'new_values_json', 'route', 'method', 'ip_address', 'user_agent', 'metadata_json'];

    protected function casts(): array
    {
        return ['old_values_json' => 'array', 'new_values_json' => 'array', 'metadata_json' => 'array'];
    }
}
