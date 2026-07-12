<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'project_id', 'property_id', 'unit_code', 'unit_type', 'status', 'floor',
        'bedrooms', 'bathrooms', 'area', 'garden_area', 'terrace_area', 'price', 'currency',
        'down_payment', 'installment_months', 'finishing_type', 'view_type', 'featured_image',
        'gallery_json', 'specs_json', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'area' => 'decimal:2',
            'garden_area' => 'decimal:2',
            'terrace_area' => 'decimal:2',
            'price' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'installment_months' => 'integer',
            'gallery_json' => 'array',
            'specs_json' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
