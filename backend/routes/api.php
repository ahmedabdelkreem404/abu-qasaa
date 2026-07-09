<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    require base_path('app/Modules/BusinessUnits/Presentation/Routes/api.php');
    require base_path('app/Modules/Catalog/Presentation/Routes/api.php');
    require base_path('app/Modules/Commerce/Presentation/Routes/api.php');
    require base_path('app/Modules/Payments/Presentation/Routes/api.php');
    require base_path('app/Modules/Inventory/Presentation/Routes/api.php');
    require base_path('app/Modules/ServicesRfq/Presentation/Routes/api.php');
    require base_path('app/Modules/RealEstate/Presentation/Routes/api.php');
    require base_path('app/Modules/CMS/Presentation/Routes/api.php');

    Route::get('/modules', fn () => response()->json(['data' => ['Core', 'Identity', 'BusinessUnits', 'Catalog', 'Commerce', 'Inventory', 'Payments', 'CMS', 'ServicesRfq', 'RealEstate', 'Notifications', 'Reports', 'Audit']]));
    Route::get('/settings', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Business unit scoped settings endpoint']]));
});
