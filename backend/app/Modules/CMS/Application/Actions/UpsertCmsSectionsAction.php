<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\CMS\Infrastructure\Models\CmsSection;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Support\Facades\DB;

class UpsertCmsSectionsAction extends BaseAction
{
    public function handle(mixed ...$arguments): CmsPage
    {
        /** @var CmsPage $page */
        $page = $arguments[0];
        $sections = $arguments[1] ?? [];

        DB::transaction(function () use ($page, $sections): void {
            $page->sections()->delete();

            foreach ($sections as $index => $section) {
                CmsSection::query()->create([
                    ...$section,
                    'cms_page_id' => $page->id,
                    'sort_order' => $section['sort_order'] ?? $index,
                    'is_active' => $section['is_active'] ?? true,
                ]);
            }
        });

        return $page->refresh()->load(['businessUnit', 'sections']);
    }
}
