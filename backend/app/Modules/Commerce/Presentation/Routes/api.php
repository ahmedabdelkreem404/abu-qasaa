<?php

use App\Modules\Commerce\Presentation\Http\Controllers\CommerceController;
use Illuminate\Support\Facades\Route;

Route::post('/public/{businessSlug}/cart', [CommerceController::class, 'getOrCreateCart']);
Route::get('/public/{businessSlug}/cart/{sessionToken}', [CommerceController::class, 'getCart']);
Route::post('/public/{businessSlug}/cart/{sessionToken}/items', [CommerceController::class, 'addCartItem']);
Route::put('/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}', [CommerceController::class, 'updateCartItem']);
Route::delete('/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}', [CommerceController::class, 'removeCartItem']);
Route::delete('/public/{businessSlug}/cart/{sessionToken}/clear', [CommerceController::class, 'clearCart']);
Route::post('/public/{businessSlug}/checkout', [CommerceController::class, 'checkout']);
Route::get('/public/{businessSlug}/orders/{orderNumber}', [CommerceController::class, 'publicOrder']);

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
