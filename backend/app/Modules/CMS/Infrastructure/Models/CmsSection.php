<?php

namespace App\Modules\CMS\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsSection extends Model
{
    protected $fillable = [
        'cms_page_id', 'section_type', 'title_ar', 'title_en', 'subtitle_ar', 'subtitle_en',
        'body_ar', 'body_en', 'image', 'button_label_ar', 'button_label_en',
        'button_url', 'data_json', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'data_json' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }
}
