<?php

namespace App\Modules\Payments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymobPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:50'],
            'payment_method_id' => ['required_without:method_key', 'nullable', 'integer', 'exists:payment_methods,id'],
            'method_key' => ['required_without:payment_method_id', 'nullable', 'string', 'max:120'],
        ];
    }
}
