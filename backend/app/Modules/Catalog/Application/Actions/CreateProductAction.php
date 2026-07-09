<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateProductAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$data, $user] = $arguments + [[], null];
        if ($user instanceof User) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
        }

        return Product::query()->create($data);
    }
}
