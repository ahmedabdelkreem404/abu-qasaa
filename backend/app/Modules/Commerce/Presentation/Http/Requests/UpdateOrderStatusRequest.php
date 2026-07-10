<?php

namespace App\Modules\Commerce\Presentation\Http\Requests;

use App\Modules\Commerce\Domain\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrderStatus::values())],
            'note' => ['nullable', 'string'],
        ];
    }
}
