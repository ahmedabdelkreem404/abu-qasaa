<?php

namespace App\Modules\Payments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Payments\Application\Actions\ListPaymobTransactionsAction;
use App\Modules\Payments\Infrastructure\Models\PaymentTransaction;
use App\Modules\Payments\Presentation\Http\Resources\PaymentTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPaymobController extends Controller
{
    public function transactions(Request $request, ListPaymobTransactionsAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (PaymentTransaction $transaction) => PaymentTransactionResource::make($transaction)->resolve()),
            'Paymob transactions retrieved successfully',
        );
    }
}
