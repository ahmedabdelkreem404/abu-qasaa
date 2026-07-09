<?php

namespace App\Modules\CMS\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cms_page_id' => $this->cms_page_id,
            'section_type' => $this->section_type,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'subtitle_ar' => $this->subtitle_ar,
            'subtitle_en' => $this->subtitle_en,
            'body_ar' => $this->body_ar,
            'body_en' => $this->body_en,
            'image' => $this->image,
            'button_label_ar' => $this->button_label_ar,
            'button_label_en' => $this->button_label_en,
            'button_url' => $this->button_url,
            'data_json' => $this->data_json ?? [],
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
