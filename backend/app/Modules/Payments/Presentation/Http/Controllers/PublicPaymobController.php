<?php

namespace App\Modules\Payments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Payments\Application\Actions\GetOrderPaymentStatusAction;
use App\Modules\Payments\Application\Actions\HandlePaymobReturnAction;
use App\Modules\Payments\Application\Actions\InitiatePaymobPaymentAction;
use App\Modules\Payments\Application\Services\PaymentScopeService;
use App\Modules\Payments\Presentation\Http\Requests\InitiatePaymobPaymentRequest;
use App\Modules\Payments\Presentation\Http\Resources\PaymobInitiationResource;
use App\Modules\Payments\Presentation\Http\Resources\PublicPaymentStatusResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPaymobController extends Controller
{
    public function __construct(private readonly PaymentScopeService $scope) {}

    public function initiate(InitiatePaymobPaymentRequest $request, string $businessSlug, string $orderNumber, InitiatePaymobPaymentAction $action): JsonResponse
    {
        $businessUnit = $this->scope->publicPaymobBusinessUnit($businessSlug);
        $order = $this->scope->publicOrder($businessUnit, $orderNumber, $request->validated('phone'));

        return ApiResponse::success(PaymobInitiationResource::make($action->handle($businessUnit, $order, $request->validated())), 'Paymob payment initiated successfully', 201);
    }

    public function status(Request $request, string $businessSlug, string $orderNumber, GetOrderPaymentStatusAction $action): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);
        $businessUnit = $this->scope->publicPaymobBusinessUnit($businessSlug);
        $order = $this->scope->publicOrder($businessUnit, $orderNumber, $request->query('phone'));

        return ApiResponse::success(PublicPaymentStatusResource::make($action->handle($order)), 'Payment status retrieved successfully');
    }

    public function return(Request $request, HandlePaymobReturnAction $action): JsonResponse
    {
        $payment = $action->handle($request->query() + $request->all());

        return ApiResponse::success(['payment_id' => $payment?->id, 'status' => $payment?->status ?? 'processing'], 'Paymob return received. Payment will be confirmed by callback.');
    }
}
