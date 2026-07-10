<?php

namespace App\Modules\Payments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualPaymentProofRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:50'],
            'payment_method_id' => ['required_without:method_key', 'nullable', 'integer', 'exists:payment_methods,id'],
            'method_key' => ['required_without:payment_method_id', 'nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'sender_account' => ['nullable', 'string', 'max:255'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'proof_image' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
