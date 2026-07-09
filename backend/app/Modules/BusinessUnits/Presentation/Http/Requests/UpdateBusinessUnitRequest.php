<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Requests;

use App\Modules\BusinessUnits\Domain\Enums\BusinessUnitStatus;
use App\Modules\Core\Domain\Enums\BusinessUnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Protect with Super Admin permissions in the auth phase.
        return true;
    }

    public function rules(): array
    {
        $businessUnit = $this->route('businessUnit');

        return [
            'parent_id' => ['nullable', 'exists:business_units,id'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('business_units', 'slug')->ignore($businessUnit?->id)],
            'type' => ['sometimes', 'required', Rule::in(BusinessUnitType::values())],
            'status' => ['sometimes', 'required', Rule::in(BusinessUnitStatus::values())],
            'logo' => ['nullable', 'string', 'max:2048'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'string', 'max:32'],
            'secondary_color' => ['nullable', 'string', 'max:32'],
            'settings_json' => ['nullable', 'array'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
