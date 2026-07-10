<?php

use App\Modules\Payments\Presentation\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::get('/public/{businessSlug}/payment-methods', [PaymentsController::class, 'publicMethods']);
Route::get('/public/{businessSlug}/orders/{orderNumber}/payment-options', [PaymentsController::class, 'publicPaymentOptions']);
Route::post('/public/{businessSlug}/orders/{orderNumber}/manual-payment-proofs', [PaymentsController::class, 'submitManualProof']);
Route::post('/public/{businessSlug}/orders/{orderNumber}/cash-on-delivery', [PaymentsController::class, 'publicCashOnDelivery']);

Route::middleware('auth:sanctum')->prefix('payments')->group(function (): void {
    Route::get('/methods', [PaymentsController::class, 'methods'])->middleware('permission:payments.manage_methods');
    Route::post('/methods', [PaymentsController::class, 'storeMethod'])->middleware('permission:payments.manage_methods');
    Route::get('/methods/{paymentMethod}', [PaymentsController::class, 'showMethod'])->middleware('permission:payments.manage_methods');
    Route::match(['put', 'patch'], '/methods/{paymentMethod}', [PaymentsController::class, 'updateMethod'])->middleware('permission:payments.manage_methods');
    Route::post('/methods/{paymentMethod}/toggle', [PaymentsController::class, 'toggleMethod'])->middleware('permission:payments.manage_methods');

    Route::get('/', [PaymentsController::class, 'payments'])->middleware('permission:payments.view');
    Route::get('/manual-proofs', [PaymentsController::class, 'manualProofs'])->middleware('permission:payments.review_manual');
    Route::get('/manual-proofs/{manualPaymentProof}', [PaymentsController::class, 'showManualProof'])->middleware('permission:payments.review_manual');
    Route::post('/manual-proofs/{manualPaymentProof}/approve', [PaymentsController::class, 'approveManualProof'])->middleware('permission:payments.review_manual');
    Route::post('/manual-proofs/{manualPaymentProof}/reject', [PaymentsController::class, 'rejectManualProof'])->middleware('permission:payments.review_manual');
    Route::post('/orders/{order}/mark-paid-manually', [PaymentsController::class, 'markOrderPaidManually'])->middleware('permission:orders.update_status');
    Route::post('/orders/{order}/cash-on-delivery', [PaymentsController::class, 'markOrderCashOnDelivery'])->middleware('permission:orders.update_status');
    Route::get('/{payment}', [PaymentsController::class, 'showPayment'])->middleware('permission:payments.view');
});
