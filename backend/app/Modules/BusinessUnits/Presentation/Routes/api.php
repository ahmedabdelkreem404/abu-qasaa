<?php

use App\Modules\BusinessUnits\Presentation\Http\Controllers\BusinessUnitController;
use Illuminate\Support\Facades\Route;

Route::get('/public/business-units', [BusinessUnitController::class, 'publicIndex']);
Route::get('/public/business-units/{slug}', [BusinessUnitController::class, 'publicShow']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/business-units', [BusinessUnitController::class, 'index'])->middleware('permission:business_units.view');
    Route::post('/business-units', [BusinessUnitController::class, 'store'])->middleware('permission:business_units.create');
    Route::get('/business-units/{businessUnit}', [BusinessUnitController::class, 'show'])->middleware('permission:business_units.view');
    Route::match(['put', 'patch'], '/business-units/{businessUnit}', [BusinessUnitController::class, 'update'])->middleware('permission:business_units.update');
    Route::delete('/business-units/{businessUnit}', [BusinessUnitController::class, 'destroy'])->middleware('permission:business_units.archive');
    Route::post('/business-units/{businessUnit}/toggle-status', [BusinessUnitController::class, 'toggleStatus'])->middleware('permission:business_units.update');
    Route::get('/business-units/{businessUnit}/modules', [BusinessUnitController::class, 'modules'])->middleware('permission:business_units.view');
    Route::put('/business-units/{businessUnit}/modules', [BusinessUnitController::class, 'updateModules'])->middleware('permission:business_units.manage_modules');
    Route::get('/business-units/{businessUnit}/settings', [BusinessUnitController::class, 'settings'])->middleware('permission:settings.view');
    Route::put('/business-units/{businessUnit}/settings', [BusinessUnitController::class, 'updateSettings'])->middleware('permission:business_units.manage_settings');

    Route::get('/activity-templates', [BusinessUnitController::class, 'activityTemplates'])->middleware('permission:business_units.view');
    Route::get('/activity-templates/{activityTemplate}', [BusinessUnitController::class, 'activityTemplate'])->middleware('permission:business_units.view');
    Route::get('/activity-modules', [BusinessUnitController::class, 'activityModules'])->middleware('permission:business_units.view');

    Route::get('/feature-flags', [BusinessUnitController::class, 'featureFlags'])->middleware('permission:settings.view');
    Route::put('/feature-flags/{featureFlag}', [BusinessUnitController::class, 'updateFeatureFlag'])->middleware('permission:settings.manage');
});
