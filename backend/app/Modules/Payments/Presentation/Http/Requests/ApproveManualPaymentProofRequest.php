<?php

namespace App\Modules\Payments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveManualPaymentProofRequest extends FormRequest
{
    public function rules(): array
    {
        return ['admin_notes' => ['nullable', 'string']];
    }
}
