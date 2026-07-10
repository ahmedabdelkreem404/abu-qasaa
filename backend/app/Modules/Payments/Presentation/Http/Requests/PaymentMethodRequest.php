<?php

namespace App\Modules\Payments\Presentation\Http\Requests;

use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    public function rules(): array
    {
        $methodId = $this->route('paymentMethod')?->id;

        return [
            'business_unit_id' => ['required', 'exists:business_units,id'],
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9_-]*$/', Rule::unique('payment_methods', 'key')->where('business_unit_id', $this->input('business_unit_id'))->ignore($methodId)],
            'type' => ['required', Rule::enum(PaymentMethodType::class)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'instructions_ar' => ['nullable', 'string'],
            'instructions_en' => ['nullable', 'string'],
            'destination_account' => ['nullable', 'string', 'max:255'],
            'destination_account_name' => ['nullable', 'string', 'max:255'],
            'config_json' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
