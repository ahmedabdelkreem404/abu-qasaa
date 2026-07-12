<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    require base_path('app/Modules/Identity/Presentation/Routes/api.php');
    require base_path('app/Modules/BusinessUnits/Presentation/Routes/api.php');
    require base_path('app/Modules/Catalog/Presentation/Routes/api.php');
    require base_path('app/Modules/Commerce/Presentation/Routes/api.php');
    require base_path('app/Modules/Payments/Presentation/Routes/api.php');
    require base_path('app/Modules/Inventory/Presentation/Routes/api.php');
    require base_path('app/Modules/ServicesRfq/Presentation/Routes/api.php');
    require base_path('app/Modules/RealEstate/Presentation/Routes/api.php');
    require base_path('app/Modules/CMS/Presentation/Routes/api.php');
    require base_path('app/Modules/Reports/Presentation/Routes/api.php');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/modules', fn () => response()->json(['success' => true, 'message' => 'Modules retrieved successfully', 'data' => ['Core', 'Identity', 'BusinessUnits', 'Catalog', 'Commerce', 'Inventory', 'Payments', 'CMS', 'ServicesRfq', 'RealEstate', 'Notifications', 'Reports', 'Audit']]))
            ->middleware('permission:business_units.view');
        Route::get('/settings', fn () => response()->json(['success' => true, 'message' => 'Settings placeholder retrieved successfully', 'data' => []]))
            ->middleware('permission:settings.view');
    });
});
