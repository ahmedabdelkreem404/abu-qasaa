<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Core\Application\Actions\BaseAction;

class GetWholesaleApplicationAction extends BaseAction
{
    public function handle(mixed ...$arguments): WholesaleApplication
    {
        return $arguments[0]->load(['businessUnit', 'customer', 'requestedPriceList', 'reviewer']);
    }
}
