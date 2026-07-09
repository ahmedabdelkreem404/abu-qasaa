<?php

namespace App\Modules\CMS\Application\Actions;

use App\Modules\CMS\Infrastructure\Models\ContactInquiry;
use App\Modules\Core\Application\Actions\BaseAction;

class SubmitContactInquiryAction extends BaseAction
{
    public function handle(mixed ...$arguments): ContactInquiry
    {
        return ContactInquiry::query()->create([
            ...$arguments[0],
            'status' => 'new',
        ]);
    }
}
