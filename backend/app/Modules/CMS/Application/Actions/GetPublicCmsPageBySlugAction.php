<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;

class GetPublicCmsPageBySlugAction extends BaseAction
{
    public function handle(mixed ...$arguments): CmsPage
    {
        $slug = $arguments[0];
        $businessUnitId = $arguments[1] ?? null;

        $query = CmsPage::query()
            ->with(['businessUnit', 'sections' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('status', 'published')
            ->where('slug', $slug);

        if ($businessUnitId === null) {
            $query->whereNull('business_unit_id');
        } else {
            $query->where('business_unit_id', $businessUnitId);
        }

        return $query->firstOrFail();
    }
}
