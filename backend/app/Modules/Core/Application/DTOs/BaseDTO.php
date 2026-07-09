<?php

namespace App\Modules\Core\Application\DTOs;

abstract readonly class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
