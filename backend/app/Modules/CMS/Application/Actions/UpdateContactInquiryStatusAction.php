<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\ContactInquiry;
use App\Modules\Core\Application\Actions\BaseAction;

class UpdateContactInquiryStatusAction extends BaseAction
{
    public function handle(mixed ...$arguments): ContactInquiry
    {
        /** @var ContactInquiry $inquiry */
        $inquiry = $arguments[0];
        $inquiry->update(['status' => $arguments[1]]);

        return $inquiry->refresh()->load('businessUnit');
    }
}
