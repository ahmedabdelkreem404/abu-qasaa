<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Payments\Application\Services\PaymentScopeService;
use App\Modules\Payments\Domain\Enums\PaymentProvider;
use App\Modules\Payments\Infrastructure\Models\PaymentTransaction;

class ListPaymobTransactionsAction
{
    public function __construct(private readonly PaymentScopeService $scope) {}

    public function handle(User $user, array $filters = [])
    {
        $paymentIds = $this->scope->scopedPayments($user)->where('provider', PaymentProvider::Paymob->value)->pluck('id');

        return PaymentTransaction::query()
            ->with('payment.order', 'payment.businessUnit')
            ->whereIn('payment_id', $paymentIds)
            ->where('provider', PaymentProvider::Paymob->value)
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['provider_status'] ?? null, fn ($query, $value) => $query->where('provider_status', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
