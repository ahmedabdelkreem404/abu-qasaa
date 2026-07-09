<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;

class ArchiveCmsPageAction extends BaseAction
{
    public function handle(mixed ...$arguments): CmsPage
    {
        /** @var CmsPage $page */
        $page = $arguments[0];
        $page->update(['status' => 'archived']);

        return $page->refresh();
    }
}
