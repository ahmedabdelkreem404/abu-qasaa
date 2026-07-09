<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

class ListPublicCmsPagesAction extends BaseAction
{
    public function handle(mixed ...$arguments): Collection
    {
        return CmsPage::query()
            ->with(['businessUnit', 'sections' => fn ($query) => $query->where('is_active', true)])
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get();
    }
}
