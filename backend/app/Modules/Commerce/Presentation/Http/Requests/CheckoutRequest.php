<?php

namespace App\Modules\Commerce\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_token' => ['required', 'string'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.recipient_name' => ['required', 'string', 'max:255'],
            'shipping_address.phone' => ['required', 'string', 'max:255'],
            'shipping_address.governorate' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['nullable', 'string', 'max:255'],
            'shipping_address.area' => ['nullable', 'string', 'max:255'],
            'shipping_address.street_address' => ['required', 'string', 'max:2048'],
            'shipping_address.building' => ['nullable', 'string', 'max:255'],
            'shipping_address.floor' => ['nullable', 'string', 'max:255'],
            'shipping_address.apartment' => ['nullable', 'string', 'max:255'],
            'shipping_address.landmark' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
