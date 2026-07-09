<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

class UpdateBrandRequest extends StoreBrandRequest
{
    public function rules(): array
    {
        return array_map(fn (array $rules) => array_map(fn ($rule) => $rule === 'required' ? 'sometimes' : $rule, $rules), parent::rules());
    }
}
