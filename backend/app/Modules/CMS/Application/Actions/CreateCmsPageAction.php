<?php

namespace App\Modules\CMS\Application\Actions;

use App\Models\User;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;

class CreateCmsPageAction extends BaseAction
{
    public function handle(mixed ...$arguments): CmsPage
    {
        $attributes = $arguments[0];
        /** @var User|null $user */
        $user = $arguments[1] ?? null;

        $attributes['created_by'] = $user?->id;
        $attributes['updated_by'] = $user?->id;

        return CmsPage::query()->create($attributes)->load(['businessUnit', 'sections']);
    }
}
