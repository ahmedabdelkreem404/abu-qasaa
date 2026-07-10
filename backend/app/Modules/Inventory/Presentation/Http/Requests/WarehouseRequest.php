<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:main,branch,returns,damaged,transit'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_ar' => ['nullable', 'string'],
            'address_en' => ['nullable', 'string'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_sellable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
