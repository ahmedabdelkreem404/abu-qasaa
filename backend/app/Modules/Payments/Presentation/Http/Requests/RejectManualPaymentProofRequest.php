<?php

namespace App\Modules\Payments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectManualPaymentProofRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rejected_reason' => ['required', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
