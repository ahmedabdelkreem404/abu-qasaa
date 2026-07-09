<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateCategoryAction extends BaseAction
{
    public function handle(mixed ...$arguments): Category
    {
        return Category::query()->create($arguments[0]);
    }
}
