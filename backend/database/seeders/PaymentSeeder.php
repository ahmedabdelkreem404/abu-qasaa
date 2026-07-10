<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Payments\Domain\Enums\PaymentMethodType;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    private const METHODS = [
        'oils' => ['vodafone_cash', 'instapay', 'bank_transfer', 'cash_on_delivery'],
        'dates' => ['vodafone_cash', 'instapay', 'bank_transfer', 'cash_on_delivery'],
        'import-export' => ['bank_transfer', 'instapay'],
        'real-estate' => ['bank_transfer', 'instapay'],
    ];

    public function run(): void
    {
        foreach (self::METHODS as $slug => $types) {
            $businessUnit = BusinessUnit::query()->where('slug', $slug)->firstOrFail();
            foreach ($types as $index => $type) {
                PaymentMethod::query()->updateOrCreate(
                    ['business_unit_id' => $businessUnit->id, 'key' => $type],
                    [
                        'type' => $type,
                        'name_ar' => $this->name($type),
                        'name_en' => str($type)->replace('_', ' ')->title()->toString(),
                        'instructions_ar' => $this->instructions($type),
                        'instructions_en' => $this->instructions($type),
                        'destination_account' => $this->destination($type),
                        'destination_account_name' => 'Abu Qasaa Placeholder',
                        'config_json' => ['phase' => 'manual_payments_foundation'],
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }

        BusinessUnit::query()->each(function (BusinessUnit $businessUnit): void {
            PaymentMethod::query()->updateOrCreate(
                ['business_unit_id' => $businessUnit->id, 'key' => 'paymob_placeholder'],
                [
                    'type' => PaymentMethodType::PaymobPlaceholder->value,
                    'name_ar' => 'Paymob Placeholder',
                    'name_en' => 'Paymob Placeholder',
                    'instructions_ar' => 'Inactive placeholder only. No Paymob integration is enabled in Phase 6.',
                    'instructions_en' => 'Inactive placeholder only. No Paymob integration is enabled in Phase 6.',
                    'is_active' => false,
                    'sort_order' => 99,
                ],
            );
        });
    }

    private function destination(string $type): ?string
    {
        return match ($type) {
            PaymentMethodType::VodafoneCash->value => '01000000000',
            PaymentMethodType::Instapay->value => 'abuqasaa@instapay',
            PaymentMethodType::BankTransfer->value => 'Bank account details placeholder',
            default => null,
        };
    }

    private function instructions(string $type): string
    {
        return match ($type) {
            PaymentMethodType::VodafoneCash->value => 'Transfer the order total to the placeholder Vodafone Cash number, then submit the transaction reference for review.',
            PaymentMethodType::Instapay->value => 'Send the order total to the placeholder Instapay handle, then submit the reference for manual review.',
            PaymentMethodType::BankTransfer->value => 'Transfer the order total to the placeholder bank account details, then submit the transfer reference.',
            PaymentMethodType::CashOnDelivery->value => 'Pay in cash when the order is delivered. The order is not marked paid until an admin confirms collection.',
            default => 'Manual payment placeholder.',
        };
    }

    private function name(string $type): string
    {
        return match ($type) {
            PaymentMethodType::VodafoneCash->value => 'Vodafone Cash',
            PaymentMethodType::Instapay->value => 'Instapay',
            PaymentMethodType::BankTransfer->value => 'Bank Transfer',
            PaymentMethodType::CashOnDelivery->value => 'Cash on Delivery',
            default => 'Payment Method',
        };
    }
}
