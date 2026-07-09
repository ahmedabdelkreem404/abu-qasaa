<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateCategoryAction extends BaseAction
{
    public function handle(mixed ...$arguments): Category
    {
        $category = $arguments[0];
        $category->update($arguments[1]);

        return $category->refresh();
    }
}
