<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Application\Services\WholesalePricingService;
use App\Modules\Core\Application\Actions\BaseAction;

class ResolveWholesaleProductPriceAction extends BaseAction
{
    public function handle(mixed ...$arguments): array
    {
        return app(WholesalePricingService::class)->resolveProductPrice(...$arguments);
    }
}
