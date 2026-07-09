<?php

namespace App\Modules\CMS\Application\Actions;

use App\Models\User;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateCmsPageAction extends BaseAction
{
    public function handle(mixed ...$arguments): CmsPage
    {
        /** @var CmsPage $page */
        $page = $arguments[0];
        $attributes = $arguments[1];
        /** @var User|null $user */
        $user = $arguments[2] ?? null;

        $attributes['updated_by'] = $user?->id;
        $page->update($attributes);

        return $page->refresh()->load(['businessUnit', 'sections']);
    }
}
