<?php

namespace App\Modules\BusinessUnits\Application\DTOs;

use App\Modules\Core\Application\DTOs\BaseDTO;

readonly class BusinessUnitDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $type,
        public string $status = 'draft',
        public ?string $description = null,
    ) {}
}
