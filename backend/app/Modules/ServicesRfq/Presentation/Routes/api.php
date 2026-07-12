<?php

use App\Modules\ServicesRfq\Presentation\Http\Controllers\ServicesRfqController;
use Illuminate\Support\Facades\Route;

Route::get('/public/{businessSlug}/services', [ServicesRfqController::class, 'publicServices']);
Route::get('/public/{businessSlug}/services/{serviceSlug}', [ServicesRfqController::class, 'publicService']);
Route::post('/public/{businessSlug}/rfq-requests', [ServicesRfqController::class, 'submitRfq']);
Route::get('/public/{businessSlug}/rfq-requests/{rfqNumber}/status', [ServicesRfqController::class, 'publicStatus']);

Route::middleware('auth:sanctum')->prefix('services-rfq')->group(function (): void {
    Route::get('/rfq-requests', [ServicesRfqController::class, 'rfqs'])->middleware('permission:rfq.view');
    Route::post('/rfq-requests/{rfqRequest}/quotations', [ServicesRfqController::class, 'createQuotation'])->middleware('permission:rfq.manage');
    Route::post('/quotations/{quotation}/send', [ServicesRfqController::class, 'sendQuotation'])->middleware('permission:rfq.manage');
});
