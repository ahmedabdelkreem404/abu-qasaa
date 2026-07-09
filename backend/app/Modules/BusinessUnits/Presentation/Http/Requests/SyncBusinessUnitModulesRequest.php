<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncBusinessUnitModulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Protect with Super Admin permissions in the auth phase.
        return true;
    }

    public function rules(): array
    {
        return [
            'modules' => ['required', 'array'],
            'modules.*.key' => ['required', 'exists:activity_modules,key'],
            'modules.*.is_enabled' => ['sometimes', 'boolean'],
            'modules.*.settings_json' => ['nullable', 'array'],
        ];
    }
}
