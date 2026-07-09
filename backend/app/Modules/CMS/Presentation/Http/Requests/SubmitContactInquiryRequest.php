<?php

namespace App\Modules\CMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitContactInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'metadata_json' => ['nullable', 'array'],
        ];
    }
}
