<?php

namespace App\Modules\Payments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Payments\Application\Actions\ApproveManualPaymentProofAction;
use App\Modules\Payments\Application\Actions\CreateManualPaymentProofAction;
use App\Modules\Payments\Application\Actions\CreatePaymentMethodAction;
use App\Modules\Payments\Application\Actions\GetManualPaymentProofAction;
use App\Modules\Payments\Application\Actions\GetPaymentAction;
use App\Modules\Payments\Application\Actions\ListManualPaymentProofsAction;
use App\Modules\Payments\Application\Actions\ListPaymentMethodsAction;
use App\Modules\Payments\Application\Actions\ListPaymentsAction;
use App\Modules\Payments\Application\Actions\ListPublicPaymentMethodsAction;
use App\Modules\Payments\Application\Actions\MarkOrderCashOnDeliveryAction;
use App\Modules\Payments\Application\Actions\MarkOrderPaidManuallyAction;
use App\Modules\Payments\Application\Actions\RejectManualPaymentProofAction;
use App\Modules\Payments\Application\Actions\TogglePaymentMethodAction;
use App\Modules\Payments\Application\Actions\UpdatePaymentMethodAction;
use App\Modules\Payments\Application\Services\PaymentScopeService;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use App\Modules\Payments\Presentation\Http\Requests\ApproveManualPaymentProofRequest;
use App\Modules\Payments\Presentation\Http\Requests\ManualPaymentProofRequest;
use App\Modules\Payments\Presentation\Http\Requests\MarkOrderPaidManuallyRequest;
use App\Modules\Payments\Presentation\Http\Requests\PaymentMethodRequest;
use App\Modules\Payments\Presentation\Http\Requests\RejectManualPaymentProofRequest;
use App\Modules\Payments\Presentation\Http\Resources\ManualPaymentProofResource;
use App\Modules\Payments\Presentation\Http\Resources\PaymentMethodResource;
use App\Modules\Payments\Presentation\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function __construct(private readonly PaymentScopeService $scope) {}

    public function publicMethods(string $businessSlug, ListPublicPaymentMethodsAction $action): JsonResponse
    {
        return ApiResponse::success($action->handle($this->scope->publicBusinessUnit($businessSlug))->map(fn (PaymentMethod $method) => (new PaymentMethodResource($method, true))->resolve()), 'Payment methods retrieved successfully');
    }

    public function publicPaymentOptions(Request $request, string $businessSlug, string $orderNumber, ListPublicPaymentMethodsAction $action): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);
        $businessUnit = $this->scope->publicBusinessUnit($businessSlug);
        $order = $this->scope->publicOrder($businessUnit, $orderNumber, $request->query('phone'));

        return ApiResponse::success([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'grand_total' => $order->grand_total,
                'currency' => $order->currency,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
            ],
            'payment_methods' => $action->handle($businessUnit)->map(fn (PaymentMethod $method) => (new PaymentMethodResource($method, true))->resolve()),
            'proofs' => ManualPaymentProofResource::collection($order->manualPaymentProofs()->with(['paymentMethod', 'payment'])->latest()->get())->resolve(),
        ], 'Payment options retrieved successfully');
    }

    public function submitManualProof(ManualPaymentProofRequest $request, string $businessSlug, string $orderNumber, CreateManualPaymentProofAction $action): JsonResponse
    {
        $businessUnit = $this->scope->publicBusinessUnit($businessSlug);
        $order = $this->scope->publicOrder($businessUnit, $orderNumber, $request->validated('phone'));

        return ApiResponse::success(new ManualPaymentProofResource($action->handle($businessUnit, $order, $request->validated()), true), 'Payment proof submitted and pending review.', 201);
    }

    public function publicCashOnDelivery(Request $request, string $businessSlug, string $orderNumber, MarkOrderCashOnDeliveryAction $action): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);
        $businessUnit = $this->scope->publicBusinessUnit($businessSlug);
        $order = $this->scope->publicOrder($businessUnit, $orderNumber, $request->input('phone'));

        return ApiResponse::success(PaymentResource::make($action->handle($order)), 'Cash on delivery selected.', 201);
    }

    public function methods(Request $request, ListPaymentMethodsAction $action): JsonResponse
    {
        return ApiResponse::paginated($action->handle($request->user(), $request->query())->through(fn (PaymentMethod $method) => PaymentMethodResource::make($method)->resolve()), 'Payment methods retrieved successfully');
    }

    public function storeMethod(PaymentMethodRequest $request, CreatePaymentMethodAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $request->validated('business_unit_id'))) {
            return $error;
        }

        return ApiResponse::success(PaymentMethodResource::make($action->handle($request->validated())), 'Payment method created successfully', 201);
    }

    public function showMethod(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $paymentMethod->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PaymentMethodResource::make($paymentMethod->load('businessUnit')), 'Payment method retrieved successfully');
    }

    public function updateMethod(PaymentMethodRequest $request, PaymentMethod $paymentMethod, UpdatePaymentMethodAction $action): JsonResponse
    {
        if (($error = $this->scope->dashboardScope($request->user(), $paymentMethod->business_unit_id)) || ($error = $this->scope->dashboardScope($request->user(), $request->validated('business_unit_id')))) {
            return $error;
        }

        return ApiResponse::success(PaymentMethodResource::make($action->handle($paymentMethod, $request->validated())), 'Payment method updated successfully');
    }

    public function toggleMethod(Request $request, PaymentMethod $paymentMethod, TogglePaymentMethodAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $paymentMethod->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PaymentMethodResource::make($action->handle($paymentMethod)), 'Payment method toggled successfully');
    }

    public function payments(Request $request, ListPaymentsAction $action): JsonResponse
    {
        return ApiResponse::paginated($action->handle($request->user(), $request->query())->through(fn (Payment $payment) => PaymentResource::make($payment)->resolve()), 'Payments retrieved successfully');
    }

    public function showPayment(Request $request, Payment $payment, GetPaymentAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $payment->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PaymentResource::make($action->handle($payment)), 'Payment retrieved successfully');
    }

    public function manualProofs(Request $request, ListManualPaymentProofsAction $action): JsonResponse
    {
        return ApiResponse::paginated($action->handle($request->user(), $request->query())->through(fn (ManualPaymentProof $proof) => ManualPaymentProofResource::make($proof)->resolve()), 'Manual payment proofs retrieved successfully');
    }

    public function showManualProof(Request $request, ManualPaymentProof $manualPaymentProof, GetManualPaymentProofAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $manualPaymentProof->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ManualPaymentProofResource::make($action->handle($manualPaymentProof)), 'Manual payment proof retrieved successfully');
    }

    public function approveManualProof(ApproveManualPaymentProofRequest $request, ManualPaymentProof $manualPaymentProof, ApproveManualPaymentProofAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $manualPaymentProof->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ManualPaymentProofResource::make($action->handle($manualPaymentProof, $request->validated(), $request->user())), 'Manual payment proof approved successfully');
    }

    public function rejectManualProof(RejectManualPaymentProofRequest $request, ManualPaymentProof $manualPaymentProof, RejectManualPaymentProofAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $manualPaymentProof->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ManualPaymentProofResource::make($action->handle($manualPaymentProof, $request->validated(), $request->user())), 'Manual payment proof rejected successfully');
    }

    public function markOrderPaidManually(MarkOrderPaidManuallyRequest $request, Order $order, MarkOrderPaidManuallyAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $order->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PaymentResource::make($action->handle($order, $request->validated(), $request->user())), 'Order marked paid manually');
    }

    public function markOrderCashOnDelivery(Request $request, Order $order, MarkOrderCashOnDeliveryAction $action): JsonResponse
    {
        if ($error = $this->scope->dashboardScope($request->user(), $order->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PaymentResource::make($action->handle($order, $request->user())), 'Cash on delivery selected for order');
    }
}
