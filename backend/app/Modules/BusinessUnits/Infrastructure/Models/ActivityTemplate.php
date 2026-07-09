<?php

namespace App\Modules\BusinessUnits\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'default_modules_json',
        'default_settings_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_modules_json' => 'array',
            'default_settings_json' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
