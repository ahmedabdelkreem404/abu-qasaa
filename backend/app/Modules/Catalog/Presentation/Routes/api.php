<?php

use App\Modules\Catalog\Presentation\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/public/{businessSlug}/products', [CatalogController::class, 'publicProducts']);
Route::get('/public/{businessSlug}/products/{productSlug}', [CatalogController::class, 'publicProduct']);
Route::get('/public/{businessSlug}/categories', [CatalogController::class, 'publicCategories']);
Route::get('/public/{businessSlug}/brands', [CatalogController::class, 'publicBrands']);

Route::middleware('auth:sanctum')->prefix('catalog')->group(function (): void {
    Route::get('/categories', [CatalogController::class, 'categories'])->middleware('permission:products.view');
    Route::post('/categories', [CatalogController::class, 'storeCategory'])->middleware('permission:products.create');
    Route::get('/categories/{category}', [CatalogController::class, 'showCategory'])->middleware('permission:products.view');
    Route::match(['put', 'patch'], '/categories/{category}', [CatalogController::class, 'updateCategory'])->middleware('permission:products.update');
    Route::delete('/categories/{category}', [CatalogController::class, 'destroyCategory'])->middleware('permission:products.delete');

    Route::get('/brands', [CatalogController::class, 'brands'])->middleware('permission:products.view');
    Route::post('/brands', [CatalogController::class, 'storeBrand'])->middleware('permission:products.create');
    Route::get('/brands/{brand}', [CatalogController::class, 'showBrand'])->middleware('permission:products.view');
    Route::match(['put', 'patch'], '/brands/{brand}', [CatalogController::class, 'updateBrand'])->middleware('permission:products.update');
    Route::delete('/brands/{brand}', [CatalogController::class, 'destroyBrand'])->middleware('permission:products.delete');

    Route::get('/products', [CatalogController::class, 'products'])->middleware('permission:products.view');
    Route::post('/products', [CatalogController::class, 'storeProduct'])->middleware('permission:products.create');
    Route::get('/products/{product}', [CatalogController::class, 'showProduct'])->middleware('permission:products.view');
    Route::match(['put', 'patch'], '/products/{product}', [CatalogController::class, 'updateProduct'])->middleware('permission:products.update');
    Route::delete('/products/{product}', [CatalogController::class, 'destroyProduct'])->middleware('permission:products.delete');
    Route::post('/products/{product}/publish', [CatalogController::class, 'publishProduct'])->middleware('permission:products.update');
    Route::put('/products/{product}/variants', [CatalogController::class, 'upsertVariants'])->middleware('permission:products.update');
    Route::put('/products/{product}/images', [CatalogController::class, 'upsertImages'])->middleware('permission:products.update');
    Route::put('/products/{product}/prices', [CatalogController::class, 'upsertPrices'])->middleware('permission:products.update');

    Route::get('/price-lists', [CatalogController::class, 'priceLists'])->middleware('permission:products.view');
    Route::post('/price-lists', [CatalogController::class, 'storePriceList'])->middleware('permission:products.create');
    Route::get('/price-lists/{priceList}', [CatalogController::class, 'showPriceList'])->middleware('permission:products.view');
    Route::match(['put', 'patch'], '/price-lists/{priceList}', [CatalogController::class, 'updatePriceList'])->middleware('permission:products.update');
    Route::delete('/price-lists/{priceList}', [CatalogController::class, 'destroyPriceList'])->middleware('permission:products.delete');
});
