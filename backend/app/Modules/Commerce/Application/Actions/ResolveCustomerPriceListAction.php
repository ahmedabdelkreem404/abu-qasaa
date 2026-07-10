<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Application\Services\WholesalePricingService;
use App\Modules\Core\Application\Actions\BaseAction;

class ResolveCustomerPriceListAction extends BaseAction
{
    public function handle(mixed ...$arguments): mixed
    {
        return app(WholesalePricingService::class)->resolvePriceList($arguments[0]);
    }
}
