<?php

namespace App\Modules\CMS\Presentation\Http\Requests;

use App\Modules\CMS\Domain\Enums\CmsSectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertCmsSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array'],
            'sections.*.section_type' => ['required', Rule::in(CmsSectionType::values())],
            'sections.*.title_ar' => ['nullable', 'string', 'max:255'],
            'sections.*.title_en' => ['nullable', 'string', 'max:255'],
            'sections.*.subtitle_ar' => ['nullable', 'string', 'max:255'],
            'sections.*.subtitle_en' => ['nullable', 'string', 'max:255'],
            'sections.*.body_ar' => ['nullable', 'string'],
            'sections.*.body_en' => ['nullable', 'string'],
            'sections.*.image' => ['nullable', 'string', 'max:2048'],
            'sections.*.button_label_ar' => ['nullable', 'string', 'max:255'],
            'sections.*.button_label_en' => ['nullable', 'string', 'max:255'],
            'sections.*.button_url' => ['nullable', 'string', 'max:2048'],
            'sections.*.data_json' => ['nullable', 'array'],
            'sections.*.sort_order' => ['nullable', 'integer'],
            'sections.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
