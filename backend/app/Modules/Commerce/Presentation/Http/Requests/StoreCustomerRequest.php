<?php

namespace App\Modules\Commerce\Presentation\Http\Requests;

use App\Modules\Commerce\Domain\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'type' => ['required', Rule::in(CustomerType::values())],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'commercial_record' => ['nullable', 'string', 'max:255'],
            'approval_status' => ['nullable', 'string', 'max:255'],
            'price_list_id' => ['nullable', 'exists:price_lists,id'],
            'notes' => ['nullable', 'string'],
            'metadata_json' => ['nullable', 'array'],
        ];
    }
}
