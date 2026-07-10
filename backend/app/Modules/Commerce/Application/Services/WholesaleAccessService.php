<?php

namespace App\Modules\Commerce\Application\Services;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Domain\Enums\WholesaleCustomerStatus;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WholesaleAccessService
{
    public function wholesaleEnabled(BusinessUnit $businessUnit): bool
    {
        $moduleEnabled = $businessUnit->moduleAssignments()
            ->whereHas('activityModule', fn ($query) => $query->where('key', 'wholesale'))
            ->where('is_enabled', true)
            ->exists();

        $value = $businessUnit->settings()->where('key', 'wholesale_enabled')->value('value');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $moduleEnabled && (bool) $value;
    }

    public function approvedCustomer(BusinessUnit $businessUnit, ?string $phone, ?string $token): ?Customer
    {
        if (! $this->wholesaleEnabled($businessUnit) || ! $phone || ! $token) {
            return null;
        }

        $customer = Customer::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('phone', $phone)
            ->where('wholesale_status', WholesaleCustomerStatus::Approved->value)
            ->first();

        if (! $customer || ! $customer->wholesale_access_token_hash || ! Hash::check($token, $customer->wholesale_access_token_hash)) {
            return null;
        }

        return $customer;
    }

    public function issueAccessToken(Customer $customer): string
    {
        $token = Str::random(48);
        $customer->forceFill(['wholesale_access_token_hash' => Hash::make($token)])->save();

        return $token;
    }
}
