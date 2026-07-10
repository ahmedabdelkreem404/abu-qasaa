<?php

use App\Modules\Commerce\Presentation\Http\Controllers\CommerceController;
use App\Modules\Commerce\Presentation\Http\Controllers\WholesaleController;
use Illuminate\Support\Facades\Route;

Route::post('/public/{businessSlug}/cart', [CommerceController::class, 'getOrCreateCart']);
Route::get('/public/{businessSlug}/cart/{sessionToken}', [CommerceController::class, 'getCart']);
Route::post('/public/{businessSlug}/cart/{sessionToken}/items', [CommerceController::class, 'addCartItem']);
Route::put('/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}', [CommerceController::class, 'updateCartItem']);
Route::delete('/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}', [CommerceController::class, 'removeCartItem']);
Route::delete('/public/{businessSlug}/cart/{sessionToken}/clear', [CommerceController::class, 'clearCart']);
Route::post('/public/{businessSlug}/checkout', [CommerceController::class, 'checkout']);
Route::get('/public/{businessSlug}/orders/{orderNumber}', [CommerceController::class, 'publicOrder']);
Route::post('/public/{businessSlug}/wholesale/apply', [WholesaleController::class, 'apply']);
Route::get('/public/{businessSlug}/wholesale/status', [WholesaleController::class, 'status']);
Route::post('/public/{businessSlug}/wholesale/access', [WholesaleController::class, 'access']);
Route::get('/public/{businessSlug}/wholesale/products', [WholesaleController::class, 'products']);
Route::get('/public/{businessSlug}/wholesale/products/{productSlug}', [WholesaleController::class, 'product']);

Route::middleware('auth:sanctum')->prefix('commerce')->group(function (): void {
    Route::get('/customers', [CommerceController::class, 'customers'])->middleware('permission:customers.view');
    Route::post('/customers', [CommerceController::class, 'storeCustomer'])->middleware('permission:customers.create');
    Route::get('/customers/{customer}', [CommerceController::class, 'showCustomer'])->middleware('permission:customers.view');
    Route::match(['put', 'patch'], '/customers/{customer}', [CommerceController::class, 'updateCustomer'])->middleware('permission:customers.update');

    Route::get('/orders', [CommerceController::class, 'orders'])->middleware('permission:orders.view');
    Route::get('/orders/{order}', [CommerceController::class, 'showOrder'])->middleware('permission:orders.view');
    Route::put('/orders/{order}/status', [CommerceController::class, 'updateOrderStatus'])->middleware('permission:orders.update_status');
    Route::post('/orders/{order}/cancel', [CommerceController::class, 'cancelOrder'])->middleware('permission:orders.manage');
});

Route::middleware('auth:sanctum')->prefix('wholesale')->group(function (): void {
    Route::get('/applications', [WholesaleController::class, 'applications'])->middleware('permission:wholesale.view');
    Route::get('/applications/{wholesaleApplication}', [WholesaleController::class, 'showApplication'])->middleware('permission:wholesale.view');
    Route::post('/applications/{wholesaleApplication}/approve', [WholesaleController::class, 'approveApplication'])->middleware('permission:wholesale.review_applications');
    Route::post('/applications/{wholesaleApplication}/reject', [WholesaleController::class, 'rejectApplication'])->middleware('permission:wholesale.review_applications');
    Route::get('/customers', [WholesaleController::class, 'customers'])->middleware('permission:wholesale.view');
    Route::get('/customers/{customer}', [WholesaleController::class, 'showCustomer'])->middleware('permission:wholesale.view');
    Route::match(['put', 'patch'], '/customers/{customer}', [WholesaleController::class, 'updateCustomer'])->middleware('permission:wholesale.manage');
    Route::post('/customers/{customer}/assign-price-list', [WholesaleController::class, 'assignPriceList'])->middleware('permission:wholesale.assign_price_lists');
    Route::post('/customers/{customer}/approve', [WholesaleController::class, 'approveCustomer'])->middleware('permission:wholesale.manage');
    Route::post('/customers/{customer}/reject', [WholesaleController::class, 'rejectCustomer'])->middleware('permission:wholesale.manage');
    Route::get('/price-lists', [WholesaleController::class, 'priceLists'])->middleware('permission:wholesale.view');
    Route::get('/customers/{customer}/pricing-preview', [WholesaleController::class, 'pricingPreview'])->middleware('permission:wholesale.view');
});
