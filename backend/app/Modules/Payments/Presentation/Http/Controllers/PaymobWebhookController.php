<?php

namespace App\Modules\Payments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Payments\Application\Actions\HandlePaymobCallbackAction;
use App\Modules\Payments\Presentation\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymobWebhookController extends Controller
{
    public function callback(Request $request, HandlePaymobCallbackAction $action): JsonResponse
    {
        $payment = $action->handle($request->query() + $request->all(), $request->headers->all());

        return ApiResponse::success(PaymentResource::make($payment), 'Paymob callback processed successfully');
    }
}
