<?php

use App\Modules\BusinessUnits\Presentation\Http\Controllers\BusinessUnitController;
use Illuminate\Support\Facades\Route;

// TODO: Add auth:sanctum and Super Admin authorization middleware in the auth phase.
Route::get('/business-units', [BusinessUnitController::class, 'index']);
Route::post('/business-units', [BusinessUnitController::class, 'store']);
Route::get('/business-units/{businessUnit}', [BusinessUnitController::class, 'show']);
Route::match(['put', 'patch'], '/business-units/{businessUnit}', [BusinessUnitController::class, 'update']);
Route::delete('/business-units/{businessUnit}', [BusinessUnitController::class, 'destroy']);
Route::post('/business-units/{businessUnit}/toggle-status', [BusinessUnitController::class, 'toggleStatus']);
Route::get('/business-units/{businessUnit}/modules', [BusinessUnitController::class, 'modules']);
Route::put('/business-units/{businessUnit}/modules', [BusinessUnitController::class, 'updateModules']);
Route::get('/business-units/{businessUnit}/settings', [BusinessUnitController::class, 'settings']);
Route::put('/business-units/{businessUnit}/settings', [BusinessUnitController::class, 'updateSettings']);

Route::get('/activity-templates', [BusinessUnitController::class, 'activityTemplates']);
Route::get('/activity-templates/{activityTemplate}', [BusinessUnitController::class, 'activityTemplate']);
Route::get('/activity-modules', [BusinessUnitController::class, 'activityModules']);

Route::get('/feature-flags', [BusinessUnitController::class, 'featureFlags']);
Route::put('/feature-flags/{featureFlag}', [BusinessUnitController::class, 'updateFeatureFlag']);

Route::get('/public/business-units', [BusinessUnitController::class, 'publicIndex']);
Route::get('/public/business-units/{slug}', [BusinessUnitController::class, 'publicShow']);
