<?php

namespace App\Modules\Payments\Application\Services;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class PaymentScopeService
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function publicBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($this->moduleEnabled($businessUnit, 'orders') && $this->moduleEnabled($businessUnit, 'payments') && $this->moduleEnabled($businessUnit, 'manual_payments'), 404);
        abort_if(! $this->settingEnabled($businessUnit, 'manual_payment_enabled'), 403);

        return $businessUnit;
    }

    public function publicOrder(BusinessUnit $businessUnit, string $orderNumber, string $phone): Order
    {
        $order = Order::query()
            ->with(['businessUnit', 'items', 'customer'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('order_number', $orderNumber)
            ->where('customer_phone', $phone)
            ->firstOrFail();

        abort_if(in_array($order->status, ['cancelled', 'archived'], true), 403);

        return $order;
    }

    public function dashboardScope(User $user, int|string $businessUnitId): ?JsonResponse
    {
        $businessUnit = BusinessUnit::query()->findOrFail($businessUnitId);
        if (! $this->accessControl->canAccessBusinessUnit($user, $businessUnit) || ! $this->moduleEnabled($businessUnit, 'payments') || ! $this->moduleEnabled($businessUnit, 'manual_payments')) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return null;
    }

    public function scopedPayments(User $user): Builder
    {
        $query = Payment::query()->with(['businessUnit', 'order', 'customer', 'paymentMethod', 'transactions']);
        if (! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
        }

        return $query;
    }

    public function scopedProofs(User $user): Builder
    {
        $query = ManualPaymentProof::query()->with(['businessUnit', 'order', 'paymentMethod', 'payment', 'reviewer']);
        if (! $user->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $user->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
        }

        return $query;
    }

    public function moduleEnabled(BusinessUnit $businessUnit, string $key): bool
    {
        return $businessUnit->moduleAssignments()->whereHas('activityModule', fn ($query) => $query->where('key', $key))->where('is_enabled', true)->exists();
    }

    public function settingEnabled(BusinessUnit $businessUnit, string $key): bool
    {
        $value = $businessUnit->settings()->where('key', $key)->value('value');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return (bool) $value;
    }
}
