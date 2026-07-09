<?php

namespace App\Modules\Catalog\Application\Actions;

use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Core\Application\Actions\BaseAction;

class ArchiveCategoryAction extends BaseAction
{
    public function handle(mixed ...$arguments): Category
    {
        $category = $arguments[0];
        $category->update(['status' => 'archived']);
        $category->delete();

        return $category;
    }
}
