<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Commerce\Domain\Enums\WholesaleApplicationStatus;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Core\Application\Actions\BaseAction;

class RejectWholesaleApplicationAction extends BaseAction
{
    public function handle(mixed ...$arguments): WholesaleApplication
    {
        [$application, $data, $user] = $arguments;
        $application->update([
            'status' => WholesaleApplicationStatus::Rejected->value,
            'reviewed_by' => $user instanceof User ? $user->id : null,
            'reviewed_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return $application->load(['businessUnit', 'customer', 'requestedPriceList', 'reviewer']);
    }
}
