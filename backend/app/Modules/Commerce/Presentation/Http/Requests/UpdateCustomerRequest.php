<?php

namespace App\Modules\Commerce\Presentation\Http\Requests;

class UpdateCustomerRequest extends StoreCustomerRequest
{
    public function rules(): array
    {
        return array_map(fn (array $rules) => array_map(fn ($rule) => $rule === 'required' ? 'sometimes' : $rule, $rules), parent::rules());
    }
}
