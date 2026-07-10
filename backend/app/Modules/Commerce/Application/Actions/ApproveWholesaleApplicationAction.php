<?php

namespace App\Modules\Commerce\Application\Actions;

use App\Models\User;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Commerce\Domain\Enums\WholesaleApplicationStatus;
use App\Modules\Commerce\Domain\Enums\WholesaleCustomerStatus;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Core\Application\Actions\BaseAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveWholesaleApplicationAction extends BaseAction
{
    public function handle(mixed ...$arguments): WholesaleApplication
    {
        [$application, $data, $user] = $arguments;

        return DB::transaction(function () use ($application, $data, $user): WholesaleApplication {
            $priceListId = $data['price_list_id'] ?? $application->requested_price_list_id;
            if ($priceListId) {
                $validPriceList = PriceList::query()
                    ->whereKey($priceListId)
                    ->where('business_unit_id', $application->business_unit_id)
                    ->where('is_active', true)
                    ->whereIn('type', ['wholesale', 'distributor', 'special'])
                    ->exists();
                if (! $validPriceList) {
                    throw ValidationException::withMessages(['price_list_id' => ['Price list must be active, wholesale-capable, and belong to the same business unit.']]);
                }
            }

            $customer = $application->customer ?: Customer::query()
                ->where('business_unit_id', $application->business_unit_id)
                ->where('phone', $application->phone)
                ->first();

            $payload = [
                'business_unit_id' => $application->business_unit_id,
                'type' => $application->company_name ? 'company' : 'shop',
                'name' => $application->applicant_name,
                'email' => $application->email,
                'phone' => $application->phone,
                'company_name' => $application->company_name ?: $application->shop_name,
                'tax_number' => $application->tax_number,
                'commercial_record' => $application->commercial_record,
                'approval_status' => 'approved',
                'wholesale_status' => WholesaleCustomerStatus::Approved->value,
                'price_list_id' => $priceListId,
                'approved_at' => now(),
                'approved_by' => $user instanceof User ? $user->id : null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
                'notes' => $data['notes'] ?? null,
            ];
            if ($customer) {
                $customer->update($payload);
            } else {
                $customer = Customer::query()->create($payload);
            }

            $application->update([
                'customer_id' => $customer->id,
                'status' => WholesaleApplicationStatus::Approved->value,
                'reviewed_by' => $user instanceof User ? $user->id : null,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            return $application->load(['businessUnit', 'customer', 'requestedPriceList', 'reviewer']);
        });
    }
}
