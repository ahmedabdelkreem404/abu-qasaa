<?php

namespace App\Modules\Identity\Infrastructure\Models;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBusinessUnit extends Model
{
    protected $fillable = [
        'user_id',
        'business_unit_id',
        'role_id',
        'role_key',
        'is_active',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
