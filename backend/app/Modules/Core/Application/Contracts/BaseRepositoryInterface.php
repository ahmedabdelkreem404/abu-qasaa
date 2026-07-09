<?php

namespace App\Modules\Core\Application\Contracts;

interface BaseRepositoryInterface
{
    public function find(int|string $id): mixed;

    public function create(array $attributes): mixed;

    public function update(int|string $id, array $attributes): mixed;
}
