<?php

namespace App\Modules\Payments\Application\Actions;

use App\Models\User;
use App\Modules\Payments\Application\Services\PaymentScopeService;

class ListManualPaymentProofsAction
{
    public function __construct(private readonly PaymentScopeService $scope) {}

    public function handle(User $user, array $filters = [])
    {
        return $this->scope->scopedProofs($user)
            ->when($filters['business_unit_id'] ?? null, fn ($query, $value) => $query->where('business_unit_id', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['method_type'] ?? null, fn ($query, $value) => $query->whereHas('paymentMethod', fn ($method) => $method->where('type', $value)))
            ->when($filters['order_number'] ?? null, fn ($query, $value) => $query->whereHas('order', fn ($order) => $order->where('order_number', 'like', "%{$value}%")))
            ->when($filters['customer_phone'] ?? null, fn ($query, $value) => $query->whereHas('order', fn ($order) => $order->where('customer_phone', 'like', "%{$value}%")))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
