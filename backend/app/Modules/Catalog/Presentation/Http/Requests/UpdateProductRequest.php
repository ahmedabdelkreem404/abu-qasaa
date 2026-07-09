<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        return array_map(fn (array $rules) => array_map(fn ($rule) => $rule === 'required' ? 'sometimes' : $rule, $rules), parent::rules());
    }
}
