<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Protect with Super Admin permissions in the auth phase.
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
