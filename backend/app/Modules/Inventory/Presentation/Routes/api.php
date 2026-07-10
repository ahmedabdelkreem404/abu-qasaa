<?php

use App\Modules\Inventory\Presentation\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/public/{businessSlug}/branches', [InventoryController::class, 'publicBranches']);
Route::get('/public/{businessSlug}/products/{productSlug}/availability', [InventoryController::class, 'publicAvailability']);

Route::middleware('auth:sanctum')->prefix('inventory')->group(function (): void {
    Route::get('/summary', [InventoryController::class, 'summary'])->middleware('permission:inventory.view');

    Route::get('/branches', [InventoryController::class, 'branches'])->middleware('permission:branches.view');
    Route::post('/branches', [InventoryController::class, 'storeBranch'])->middleware('permission:branches.manage');
    Route::get('/branches/{branch}', [InventoryController::class, 'showBranch'])->middleware('permission:branches.view');
    Route::match(['put', 'patch'], '/branches/{branch}', [InventoryController::class, 'updateBranch'])->middleware('permission:branches.manage');
    Route::delete('/branches/{branch}', [InventoryController::class, 'destroyBranch'])->middleware('permission:branches.manage');

    Route::get('/warehouses', [InventoryController::class, 'warehouses'])->middleware('permission:warehouses.view');
    Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->middleware('permission:warehouses.manage');
    Route::get('/warehouses/{warehouse}', [InventoryController::class, 'showWarehouse'])->middleware('permission:warehouses.view');
    Route::match(['put', 'patch'], '/warehouses/{warehouse}', [InventoryController::class, 'updateWarehouse'])->middleware('permission:warehouses.manage');
    Route::delete('/warehouses/{warehouse}', [InventoryController::class, 'destroyWarehouse'])->middleware('permission:warehouses.manage');

    Route::get('/stock-items', [InventoryController::class, 'stockItems'])->middleware('permission:inventory.view');
    Route::get('/stock-items/{stockItem}', [InventoryController::class, 'showStockItem'])->middleware('permission:inventory.view');
    Route::post('/stock-items/receive', [InventoryController::class, 'receive'])->middleware('permission:inventory.adjust');
    Route::post('/stock-items/adjust', [InventoryController::class, 'adjust'])->middleware('permission:inventory.adjust');
    Route::get('/movements', [InventoryController::class, 'movements'])->middleware('permission:inventory.view');

    Route::get('/transfers', [InventoryController::class, 'transfers'])->middleware('permission:inventory.view');
    Route::post('/transfers', [InventoryController::class, 'storeTransfer'])->middleware('permission:inventory.transfer');
    Route::get('/transfers/{transfer}', [InventoryController::class, 'showTransfer'])->middleware('permission:inventory.view');
    Route::post('/transfers/{transfer}/approve', [InventoryController::class, 'approveTransfer'])->middleware('permission:inventory.transfer');
    Route::post('/transfers/{transfer}/complete', [InventoryController::class, 'completeTransfer'])->middleware('permission:inventory.transfer');
    Route::post('/transfers/{transfer}/cancel', [InventoryController::class, 'cancelTransfer'])->middleware('permission:inventory.transfer');

    Route::post('/orders/{order}/fulfill-stock', [InventoryController::class, 'fulfillOrder'])->middleware('permission:inventory.adjust');
});
