<?php

namespace App\Modules\CMS\Infrastructure\Models;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_unit_id', 'title_ar', 'title_en', 'slug', 'page_type', 'status',
        'excerpt_ar', 'excerpt_en', 'content_ar', 'content_en',
        'seo_title_ar', 'seo_title_en', 'seo_description_ar', 'seo_description_en',
        'featured_image', 'sort_order', 'published_at', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CmsSection::class)->orderBy('sort_order');
    }
}
