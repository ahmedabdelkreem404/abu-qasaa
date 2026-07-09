<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateProductAction extends BaseAction
{
    public function handle(mixed ...$arguments): Product
    {
        [$product, $data, $user] = $arguments + [null, [], null];
        if ($user instanceof User) {
            $data['updated_by'] = $user->id;
        }
        $product->update($data);

        return $product->refresh();
    }
}
